<?php
// AI translation for projects, SEO-aware drafts, SEO checklist markup, repo clean-up.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'sk-test', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\{Http, Cache}; use App\Models\{AiGeneration, Project}; use App\Services\Ai\AiWriter;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 400) : '') . "\n"; $ok ? $pass++ : $fail++; };
$fake = function (array $stubs) { Http::swap(new Illuminate\Http\Client\Factory()); Http::fake($stubs); };
$reply = fn (array $data) => Http::response(['id' => 'm', 'model' => 'claude-opus-5-5', 'content' => [['type' => 'text', 'text' => json_encode($data)]], 'stop_reason' => 'end_turn', 'usage' => ['input_tokens' => 300, 'output_tokens' => 200]], 200);
$ctl = $app->make(App\Http\Controllers\AiController::class);
$mk = function ($data) use ($app) { $req = Request::create('/x', 'POST', $data); $req->setLaravelSession($app['session']->driver()); $app->instance('request', $req); return $req; };
$sent = function () { $last = null; foreach (Http::recorded() as [$req]) { if (str_contains($req->url(), 'anthropic')) $last = $req; } return $last ? $last->data() : null; };

echo "== project translation (server)\n";
$fake(['api.anthropic.com/*' => $reply(['pt' => ['title' => 'Galeria de jogos', 'excerpt' => 'Plataforma conceptual de jogos.', 'body_markdown' => "## Contexto\n\nDetalhes."], 'es' => ['title' => 'Galería de juegos', 'excerpt' => 'Plataforma conceptual de juegos.', 'body_markdown' => "## Contexto\n\nDetalles."]])]);
$id = $ctl->translate($mk(['kind' => 'project', 'title' => 'GameHaven', 'excerpt' => 'A conceptual gaming platform.', 'body' => "## Context\n\nDetails about the project.", 'locales' => ['pt', 'es']]))->getData()->id;
$job = AiGeneration::find($id);
$check('project job is created with its kind stored', $job->status === 'pending' && $job->params['kind'] === 'project');
app(AiWriter::class)->run($job);
$st = $ctl->status($id)->getData(true);
$check('job finishes with pt + es results mapped to title / excerpt / body', $st['status'] === 'done' && $st['result']['pt']['title'] === 'Galeria de jogos' && $st['result']['es']['excerpt'] === 'Plataforma conceptual de juegos.' && str_contains($st['result']['es']['body'], 'Detalles'), json_encode($st));
$req = $sent(); $u = $req['messages'][0]['content']; $sys = $req['system'];
$check('prompt says project descriptions, labels the parts SHORT DESCRIPTION / DETAILS', str_contains($sys, 'portfolio project descriptions') && str_contains($u, 'SHORT DESCRIPTION: A conceptual gaming platform.') && str_contains($u, 'DETAILS (Markdown):') && ! str_contains($u, 'BODY (Markdown)') && str_contains($u, '<content>'));
$check('prompt protects product / technology names and forbids inventing content', str_contains($sys, 'programming language and technology names') && str_contains($sys, 'Never invent content') && str_contains($sys, 'return an empty string'));
$check('schema asks for both languages, nothing else', array_keys($req['output_config']['format']['schema']['properties']) === ['pt', 'es'] && $req['output_config']['format']['schema']['required'] === ['pt', 'es']);

$fake(['api.anthropic.com/*' => $reply(['pt' => ['title' => 'T-pt', 'excerpt' => 'Só descrição.', 'body_markdown' => ''], 'es' => ['title' => 'T-es', 'excerpt' => 'Solo descripción.', 'body_markdown' => '']])]);
$id = $ctl->translate($mk(['kind' => 'project', 'title' => 'MAE', 'excerpt' => 'Only a short description.', 'body' => null, 'locales' => ['pt', 'es']]))->getData()->id;
app(AiWriter::class)->run(AiGeneration::find($id));
$st = $ctl->status($id)->getData(true); $u = $sent()['messages'][0]['content'];
$check('a project with no details is accepted; the empty part is not sent to the AI', $st['status'] === 'done' && ! str_contains($u, 'DETAILS') && str_contains($u, 'SHORT DESCRIPTION'), json_encode($st));
$check('empty parts come back as empty strings, not null/undefined', $st['result']['pt']['body'] === '' && $st['result']['pt']['excerpt'] === 'Só descrição.');

$fake(['api.anthropic.com/*' => $reply(['pt' => ['title' => 'T', 'excerpt' => '', 'body_markdown' => 'Só detalhes'], 'es' => ['title' => 'T', 'excerpt' => '', 'body_markdown' => 'Solo detalles']])]);
$id = $ctl->translate($mk(['kind' => 'project', 'title' => 'Only notes', 'body' => 'Just the details.', 'locales' => ['pt', 'es']]))->getData()->id;
app(AiWriter::class)->run(AiGeneration::find($id)); $u = $sent()['messages'][0]['content'];
$check('details-only project: no short-description part sent', ! str_contains($u, 'SHORT DESCRIPTION') && str_contains($u, 'DETAILS (Markdown):'));

$fake(['api.anthropic.com/*' => $reply(['pt' => ['title' => 'T', 'excerpt' => 'E', 'body_markdown' => 'B'], 'es' => ['title' => 'T', 'excerpt' => 'E', 'body_markdown' => 'B']])]);
try { $ctl->translate($mk(['title' => 'A post', 'body' => '', 'locales' => ['pt']])); $check('post without body rejected', false); }
catch (Illuminate\Validation\ValidationException $e) { $check('a blog post still requires its body', isset($e->errors()['body'])); }
try { $ctl->translate($mk(['kind' => 'project', 'excerpt' => 'x', 'locales' => ['pt']])); $check('project without title rejected', false); }
catch (Illuminate\Validation\ValidationException $e) { $check('a project still requires its title', isset($e->errors()['title'])); }
try { $ctl->translate($mk(['kind' => 'weird', 'title' => 't', 'body' => 'b', 'locales' => ['pt']])); $check('unknown kind rejected', false); }
catch (Illuminate\Validation\ValidationException $e) { $check('an unknown kind is rejected', isset($e->errors()['kind'])); }
try { $ctl->translate($mk(['kind' => 'project', 'title' => 't', 'excerpt' => str_repeat('x', 5001), 'locales' => ['pt']])); $check('over-long description rejected', false); }
catch (Illuminate\Validation\ValidationException $e) { $check('very long text is capped (description 5000 chars)', isset($e->errors()['excerpt'])); }
$id = $ctl->translate($mk(['title' => 'Post', 'excerpt' => 'Ex', 'body' => 'Body text', 'locales' => ['pt', 'es']]))->getData()->id;
app(AiWriter::class)->run(AiGeneration::find($id)); $u = $sent()['messages'][0]['content']; $sys = $sent()['system'];
$check('blog-post translation unchanged: EXCERPT / BODY labels and "blog posts" wording', str_contains($u, 'EXCERPT: Ex') && str_contains($u, 'BODY (Markdown):') && str_contains($sys, 'translate blog posts'));

echo "== project form (admin page)\n";
$jar = [];
$adminGet = function (string $path) use ($kernel, $app) {
    $req = Request::create('https://franciscowebdev.com' . $path);
    $req->setLaravelSession($app['session']->driver()); $req->session()->put('is_admin', true); $req->session()->put('admin_last_seen', time());
    return $kernel->handle($req);
};
$p = Project::create(['title' => 'GameHaven', 'slug' => 'gamehaven', 'description' => 'Desc', 'notes' => 'Notes']);
foreach (['/admin/projects/create', '/admin/projects/' . $p->id . '/edit'] as $path) {
    $r = $adminGet($path); $h = $r->getContent();
    $check("$path renders with the translate button", $r->getStatusCode() === 200 && str_contains($h, 'id="project-translate"') && str_contains($h, 'Translate English to Portuguese') && str_contains($h, 'data-translate-url="https://franciscowebdev.com/admin/ai/translate"') && str_contains($h, 'data-status-url="https://franciscowebdev.com/admin/ai"'), (string) $r->getStatusCode());
    $check("$path has ids for every language field and a polite status region", preg_match_all('/id="p-(en|pt|es)-(title|description|notes)"/', $h) === 9 && str_contains($h, 'id="project-translate-status" role="status" aria-live="polite"'));
    // title, description and case study in three languages; the written version of the video joins them once the project exists
    $check("$path keeps the fields data-safe (firewall-proof)", substr_count($h, 'data-safe') === (str_contains($path, '/edit') ? 12 : 9));
}
config(['ai.api_key' => null]);
$h = $adminGet('/admin/projects/create')->getContent();
$check('button is disabled with a hint when no AI key is set', preg_match('/id="project-translate"[^>]*disabled/', $h) === 1 && str_contains($h, 'Add an AI key'));
config(['ai.api_key' => 'sk-test']);

echo "== SEO: AI instructions\n";
$draft = ['title' => 'Accessible project gallery', 'excerpt' => str_repeat('e', 130), 'focus_keyword' => 'accessible project gallery', 'tags' => ['A11y'], 'body_markdown' => '## Intro', 'notes_for_author' => ''];
$fake(['api.anthropic.com/*' => $reply($draft)]);
Cache::flush();
$id = $ctl->draft($mk(['topic' => 'A long enough topic about accessible galleries', 'type' => 'accessibility', 'tone' => 'friendly', 'length' => 'short']))->getData()->id;
app(AiWriter::class)->run(AiGeneration::find($id));
$req = $sent(); $sys = $req['system']; $schema = $req['output_config']['format']['schema'];
$check('system prompt contains the SEO rules', str_contains($sys, 'ONE focus keyword') && str_contains($sys, 'at most 55 characters') && str_contains($sys, '120 to 155 characters') && str_contains($sys, 'descriptive "##" headings') && str_contains($sys, 'never invent a URL') && str_contains($sys, '2 to 5 short'));
$check('title limit allows for the " | Francisco Cavaco" suffix', str_contains($sys, '" | Francisco Cavaco" added'));
$check('old 70-character title advice is gone', ! str_contains(json_encode($schema), 'about 70') && str_contains($schema['properties']['title']['description'], '55') && str_contains($schema['properties']['excerpt']['description'], '120 to 155'));
$check('schema asks for a focus_keyword and requires it', isset($schema['properties']['focus_keyword']) && in_array('focus_keyword', $schema['required'], true));
$st = $ctl->status($id)->getData(true);
$check('focus keyword is returned to the editor', $st['status'] === 'done' && $st['result']['focus_keyword'] === 'accessible project gallery', json_encode($st['result'] ?? []));
$fake(['api.anthropic.com/*' => $reply(array_diff_key($draft, ['focus_keyword' => 1]))]);
$id = $ctl->draft($mk(['topic' => 'Another long enough topic here', 'type' => 'tutorial', 'tone' => 'concise', 'length' => 'short']))->getData()->id;
app(AiWriter::class)->run(AiGeneration::find($id));
$check('a reply without focus_keyword still works (empty string)', $ctl->status($id)->getData(true)['result']['focus_keyword'] === '');

echo "== SEO checklist + files\n";
$h = $adminGet('/admin/posts/create')->getContent();
$check('post form has the SEO & readiness panel with a list the script fills', str_contains($h, 'SEO &amp; readiness') && str_contains($h, 'id="seo-list"') && str_contains($h, 'aria-labelledby="seo-title"'));
$check('blog-tables.sql is removed from the project', ! file_exists('blog-tables.sql') && trim((string) shell_exec('git ls-files blog-tables.sql 2>NUL')) === '');
$js = file_get_contents('resources/js/admin.js');
$check('admin bundle includes the project translate script', str_contains($js, "import './admin-project-translate'") && str_contains($js, "import './safe-submit'"));
$manifest = file_get_contents('public/build/manifest.json');
$check('build is current (contains the new modules)', str_contains(file_get_contents(glob('public/build/assets/admin-*.js')[0]), 'project-translate'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
