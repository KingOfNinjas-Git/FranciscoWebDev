<?php
// Plan 8: AI alt text for pictures, IndexNow pings on publish, Markdown preview and autosave wiring in the admin.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'AI_PROVIDER' => 'gemini', 'GEMINI_API_KEY' => 'test-gemini-key', 'AI_GEMINI_MODEL' => 'gemini-test', 'AI_DAILY_LIMIT' => '30', 'ANTHROPIC_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\{Cache, Http}; use App\Models\{AiGeneration, Post, Project, ProjectImage}; use App\Services\Ai\{ClaudeClient, GeminiClient};
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$fake = function (array $stubs) { Http::swap(new Illuminate\Http\Client\Factory()); Http::fake($stubs); };
$send = function (string $method, string $path, array $data = [], bool $admin = true) use ($kernel, $app) {
    app('cookie')->flushQueuedCookies();
    $app['session']->driver()->flush();
    $req = Request::create('https://franciscowebdev.com' . $path, $method, $data);
    if ($admin) { $req->setLaravelSession($app['session']->driver()); $req->session()->start(); $req->session()->put('is_admin', true); $req->session()->put('admin_last_seen', time()); $req->request->set('_token', $req->session()->token()); }
    $res = $kernel->handle($req);
    $kernel->terminate($req, $res); // "after the response" jobs run here
    // a real request is its own PHP process; here, forget jobs that already ran so they do not run again
    (new ReflectionProperty($app, 'terminatingCallbacks'))->setValue($app, []);

    return $res;
};

$dir = public_path('imgs/_admin_tools'); @mkdir($dir, 0755, true);
$im = imagecreatetruecolor(1600, 1000); imagefilledrectangle($im, 0, 0, 800, 500, imagecolorallocate($im, 30, 90, 200)); imagewebp($im, "$dir/shot.webp", 80);
$project = Project::create(['title' => 'Alpha', 'slug' => 'alpha', 'description' => 'A demo shop']);
$image = ProjectImage::create(['project_id' => $project->id, 'path' => 'imgs/_admin_tools/shot.webp', 'position' => 0]);

echo "== AI alt text\n";
$gem = fn (string $json) => Http::response(['candidates' => [['content' => ['role' => 'model', 'parts' => [['text' => $json]]], 'finishReason' => 'STOP']], 'usageMetadata' => ['promptTokenCount' => 300, 'candidatesTokenCount' => 60], 'modelVersion' => 'gemini-test'], 200);
$url = 'generativelanguage.googleapis.com/v1beta/models/gemini-test:generateContent';
$fake([$url => $gem(json_encode(['alt_en' => '  "Home page of an online shop with a product grid."  ', 'alt_pt' => 'Página inicial de uma loja online com grelha de produtos.', 'alt_es' => 'Página de inicio de una tienda online con una cuadrícula de productos.']))]);
$r = $send('POST', '/admin/ai/alt', ['image_id' => $image->id]); $j = json_decode($r->getContent(), true);
$check('returns alt text in three languages, tidied (quotes and spaces trimmed)', $r->getStatusCode() === 200 && $j['alt']['en'] === 'Home page of an online shop with a product grid.' && str_starts_with($j['alt']['pt'], 'Página inicial') && str_starts_with($j['alt']['es'], 'Página de inicio'), $r->getContent());
$sent = null; Http::assertSent(function ($req) use (&$sent) { $sent = $req->data(); return true; });
$parts = $sent['contents'][0]['parts'] ?? [];
$jpeg = isset($parts[0]['inlineData']) ? base64_decode($parts[0]['inlineData']['data']) : '';
$info = $jpeg ? getimagesizefromstring($jpeg) : null;
$check('Gemini gets the picture as a small JPEG next to the instructions', ($parts[0]['inlineData']['mimeType'] ?? '') === 'image/jpeg' && $info && $info[0] === 960 && $info['mime'] === 'image/jpeg' && str_contains($parts[1]['text'] ?? '', 'Alpha') && str_contains($parts[1]['text'] ?? '', 'A demo shop'));
$check('the AI is told the rules (no "Image of", 140 characters, never guess)', str_contains($sent['systemInstruction']['parts'][0]['text'], '140') && str_contains($sent['systemInstruction']['parts'][0]['text'], 'Image of') && str_contains($sent['systemInstruction']['parts'][0]['text'], 'Never guess'));
$log = AiGeneration::where('kind', 'alt')->first();
$check('the call is logged and counts toward the daily AI limit', $log && $log->status === 'done' && $log->input_tokens === 300 && AiGeneration::count() === 1);

config(['ai.daily_limit' => 1]);
$fake([$url => $gem('{}')]);
$r = $send('POST', '/admin/ai/alt', ['image_id' => $image->id]);
$check('stops at the daily limit without calling the AI', $r->getStatusCode() === 429 && str_contains($r->getContent(), 'Daily AI limit'));
Http::assertNothingSent();
config(['ai.daily_limit' => 30]);
$r = $send('POST', '/admin/ai/alt', ['image_id' => 9999]);
$check('unknown image -> 404', $r->getStatusCode() === 404);
$r = $send('POST', '/admin/ai/alt', []);
$check('missing image id -> validation error', $r->getStatusCode() === 422 || $r->getStatusCode() === 302);
$fake([$url => Http::response(['error' => ['message' => 'Quota exceeded']], 429)]);
$r = $send('POST', '/admin/ai/alt', ['image_id' => $image->id]);
$check('AI failure -> a readable message, nothing stored', $r->getStatusCode() === 502 && isset(json_decode($r->getContent(), true)['message']) && AiGeneration::where('kind', 'alt')->count() === 1);
$check('only the logged-in admin can use it', in_array($send('POST', '/admin/ai/alt', ['image_id' => $image->id], false)->getStatusCode(), [302, 301, 419]));
unlink("$dir/shot.webp"); touch("$dir/broken.webp"); $broken = ProjectImage::create(['project_id' => $project->id, 'path' => 'imgs/_admin_tools/broken.webp', 'position' => 1]);
$fake([$url => $gem('{}')]);
$r = $send('POST', '/admin/ai/alt', ['image_id' => $broken->id]);
$check('a missing or unreadable picture gives a clear message', $r->getStatusCode() === 502 && str_contains($r->getContent(), 'could not be read'));
Http::assertNothingSent();

echo "== Claude gets the picture too\n";
config(['ai.api_key' => 'k', 'ai.model' => 'claude-test', 'ai.api_url' => 'https://api.anthropic.com/v1/messages']);
$fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => '{"a":"ok"}']], 'stop_reason' => 'end_turn', 'usage' => ['input_tokens' => 5, 'output_tokens' => 2], 'model' => 'claude-test'], 200)]);
(new ClaudeClient())->structured('sys', 'describe it', ['type' => 'object', 'properties' => ['a' => ['type' => 'string']]], 100, ['mime' => 'image/jpeg', 'data' => base64_encode('JPEGDATA')]);
Http::assertSent(function ($req) { $c = $req->data()['messages'][0]['content']; return is_array($c) && $c[0]['type'] === 'image' && $c[0]['source']['type'] === 'base64' && $c[0]['source']['media_type'] === 'image/jpeg' && $c[0]['source']['data'] === base64_encode('JPEGDATA') && $c[1] === ['type' => 'text', 'text' => 'describe it']; });
$check('Claude request carries an image block followed by the text', true);
(new ClaudeClient())->structured('sys', 'plain', ['type' => 'object', 'properties' => ['a' => ['type' => 'string']]], 100);
Http::assertSent(fn ($req) => $req->data()['messages'][0]['content'] === 'plain');
$check('requests without a picture are unchanged (plain text content)', true);
config(['ai.api_key' => 'test-gemini-key', 'ai.model' => 'gemini-test']);

echo "== the editor screens\n";
$edit = $send('GET', '/admin/projects/' . $project->id . '/edit')->getContent();
$check('each picture has the AI suggestion button when the AI is set up', str_contains($edit, 'data-suggest-alt="' . $image->id . '"') && str_contains($edit, route('admin.ai.alt')));
config(['ai.api_key' => '']);
$check('the button is hidden when there is no AI key', ! str_contains($send('GET', '/admin/projects/' . $project->id . '/edit')->getContent(), 'data-suggest-alt'));
config(['ai.api_key' => 'test-gemini-key']);
$check('project form: autosave on, and a Markdown preview for the case study in each language', str_contains($edit, '<form action') && preg_match('/<form [^>]*data-autosave/', $edit) && substr_count($edit, 'data-notes-preview') === 3 && str_contains($edit, 'data-for="p-en-notes"') && str_contains($edit, 'Case study (English)'));
$check('post form has autosave', (bool) preg_match('/<form id="post-form" data-autosave/', $send('GET', '/admin/posts/create')->getContent()));
$lead = App\Models\Lead::create(['name' => 'Ana', 'email' => 'a@b.c', 'project_type' => 'business', 'description' => 'x']);
$check('lead follow-up form has autosave', (bool) preg_match('/<form [^>]*data-autosave/', $send('GET', '/admin/leads/' . $lead->id)->getContent()));
$r = $send('POST', '/admin/posts/preview', ['body' => "## Goal\nBuild **it**.\n\n<script>alert(1)</script>"]); $html = json_decode($r->getContent(), true)['html'] ?? '';
$check('the preview the project form uses renders Markdown and strips scripts', str_contains($html, '<h2>Goal</h2>') && str_contains($html, '<strong>it</strong>') && ! str_contains($html, '<script'));
$b64 = 'b64:' . base64_encode("## Encoded\nWith `code`");
$html = json_decode($send('POST', '/admin/posts/preview', ['_b64' => ['body' => $b64]])->getContent(), true)['html'] ?? '';
$check('...also when the browser sends the text encoded (firewall-safe)', str_contains($html, '<h2>Encoded</h2>'));
$js = file_get_contents('resources/js/admin.js');
$check('the admin script loads the three new tools', str_contains($js, "'./admin-image-alt'") && str_contains($js, "'./admin-notes-preview'") && str_contains($js, "'./admin-autosave'"));
$auto = file_get_contents('resources/js/admin-autosave.js');
$check('autosave remembers field names up front (the firewall-safe submit removes them briefly)', str_contains($auto, 'const keys = new Map') && str_contains($auto, "localStorage") && str_contains($auto, 'Restore it') && str_contains($auto, 'Discard'));

echo "== IndexNow\n";
$key = config('portfolio.indexnow_key');
$check('there is a 32-character key', is_string($key) && preg_match('/^[a-f0-9]{32}$/', $key) === 1);
$r = $send('GET', "/$key.txt", [], false);
$check('the key is served as plain text at /<key>.txt (proof of ownership)', $r->getStatusCode() === 200 && $r->getContent() === $key && str_starts_with((string) $r->headers->get('Content-Type'), 'text/plain'));
$check('any other key file is a 404', $send('GET', '/' . str_repeat('a', 32) . '.txt', [], false)->getStatusCode() === 404 && $send('GET', '/notes.txt', [], false)->getStatusCode() === 404 && str_contains(file_get_contents('public/robots.txt'), 'Disallow: /admin'));

$pingRequests = fn () => Http::recorded()->filter(fn ($pair) => str_contains($pair[0]->url(), 'indexnow'))->map(fn ($pair) => $pair[0]->data())->values();
$pings = fn () => $pingRequests()->count();
$fake(['api.indexnow.org/*' => Http::response('', 202)]);
$r = $send('POST', '/admin/posts', ['title' => 'Live post', 'body' => 'Hello world', 'status' => 'published']);
$flash = $r->getSession()->get('success') ?? '';
$posted = $pingRequests()->last();
$check('publishing a post pings IndexNow with its page in all three languages', $posted && $posted['host'] === 'franciscowebdev.com' && $posted['key'] === $key && $posted['keyLocation'] === "https://franciscowebdev.com/$key.txt" && $posted['urlList'] === ['https://franciscowebdev.com/blog/live-post', 'https://franciscowebdev.com/pt/blog/live-post', 'https://franciscowebdev.com/es/blog/live-post'], json_encode($posted));
$check('the admin is told', str_contains($flash, 'Post saved.') && str_contains($flash, 'IndexNow'));
$fake(['api.indexnow.org/*' => Http::response('', 202)]);
$send('POST', '/admin/posts', ['title' => 'Draft post', 'body' => 'Hello', 'status' => 'draft']);
$send('POST', '/admin/posts', ['title' => 'Future post', 'body' => 'Hello', 'status' => 'published', 'published_at' => now()->addDays(3)->toDateTimeString()]);
$check('drafts and scheduled posts are not announced', $pings() === 0);
$live = Post::where('slug', 'live-post')->first();
$fake(['api.indexnow.org/*' => Http::response('', 202)]);
$send('PUT', '/admin/posts/' . $live->id, ['title' => 'Live post', 'body' => 'Edited', 'status' => 'published']);
$check('editing a live post announces the change again', $pings() === 1);
$fake(['api.indexnow.org/*' => Http::response('', 202)]);
$r = $send('PUT', '/admin/projects/' . $project->id, ['title' => 'Alpha', 'description' => 'Changed']);
$posted = $pingRequests()->last();
$check('saving a project announces its three pages', $posted && $posted['urlList'] === ['https://franciscowebdev.com/projects/alpha', 'https://franciscowebdev.com/pt/projects/alpha', 'https://franciscowebdev.com/es/projects/alpha'], json_encode($posted));
$fake(['api.indexnow.org/*' => Http::response('boom', 500)]);
$r = $send('PUT', '/admin/posts/' . $live->id, ['title' => 'Live post', 'body' => 'Edited again', 'status' => 'published']);
$check('a failing IndexNow never breaks saving', $r->getStatusCode() === 302 && Post::find($live->id)->body === 'Edited again');
$fake(['api.indexnow.org/*' => fn () => throw new Illuminate\Http\Client\ConnectionException('no network')]);
$r = $send('PUT', '/admin/posts/' . $live->id, ['title' => 'Live post', 'body' => 'Offline edit', 'status' => 'published']);
$check('...nor does being offline', $r->getStatusCode() === 302 && Post::find($live->id)->body === 'Offline edit');
config(['app.env' => 'local']); $app['env'] = 'local';
$fake(['api.indexnow.org/*' => Http::response('', 202)]);
$send('PUT', '/admin/posts/' . $live->id, ['title' => 'Live post', 'body' => 'Local edit', 'status' => 'published']);
$check('nothing is sent from a development machine', $pings() === 0);
$app['env'] = 'production'; config(['app.env' => 'production']);

foreach (glob("$dir/*") as $f) @unlink($f); @rmdir($dir);
echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
