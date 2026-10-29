<?php
// The admin editor sends text as base64 so the host firewall cannot mistake code samples for an attack.
chdir($argv[1]);
$postFile = $argv[2] ?? __DIR__ . '/fixtures/code-sample-post.md';
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'database', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'ADMIN_PASSWORD' => 'pw', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => '', 'SESSION_SECURE_COOKIE' => 'false'] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\Cache; use App\Models\{Post, Project}; use App\Http\Middleware\DecodeSafeFields;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$post = file_get_contents($postFile);

// what the browser would send: produced by the real JavaScript helper, run in node
$js = <<<'JS'
import { toB64, wrapPayload, encodedFieldName } from './resources/js/safe-encode.js';
import fs from 'node:fs';
const text = fs.readFileSync(process.argv[2], 'utf8');
console.log(JSON.stringify({
  body: toB64(text),
  mixed: toB64('Ünïcödé — “quotes” 日本語 🎉'),
  empty: wrapPayload({ body: '', title: 'T' }, ['body', 'title']),
  wrapped: wrapPayload({ title: 'Hi', body: 'x', locales: ['pt'], n: 3 }, ['title', 'body']),
  names: [encodedFieldName('title'), encodedFieldName('translations[pt][body]'), encodedFieldName('a[b]')],
}));
JS;
file_put_contents('safe_probe.mjs', $js);
$out = json_decode(shell_exec('node safe_probe.mjs ' . escapeshellarg($postFile)), true);
unlink('safe_probe.mjs');
$check('node helper produced output', is_array($out));

echo "== JavaScript helper (run in node)\n";
$check('base64 of the whole post decodes back byte for byte', base64_decode(substr($out['body'], 4)) === $post);
$check('unicode, quotes and emoji survive', base64_decode(substr($out['mixed'], 4)) === 'Ünïcödé — “quotes” 日本語 🎉');
$check('encoded text has nothing a firewall would flag (no <, >, (, ), =, quotes, backticks)', preg_match('/^b64:[A-Za-z0-9+\/=]+$/', $out['body']) === 1);
$check('empty fields are left alone, others wrapped', ! isset($out['empty']['_b64']) && $out['empty']['body'] === '' && $out['empty']['title'] === 'T' || isset($out['empty']['_b64']['title']));
$check('wrapPayload keeps other keys plain', $out['wrapped']['locales'] === ['pt'] && $out['wrapped']['n'] === 3 && ! isset($out['wrapped']['title']) && isset($out['wrapped']['_b64']['body']));
$check('field names keep their nesting under _b64', $out['names'] === ['_b64[title]', '_b64[translations][pt][body]', '_b64[a][b]'], json_encode($out['names']));

echo "== DecodeSafeFields middleware\n";
$run = function (array $input, $json = false) use ($app) {
    $req = Request::create('/x', 'POST', $json ? [] : $input, [], [], $json ? ['CONTENT_TYPE' => 'application/json'] : [], $json ? json_encode($input) : null);
    (new DecodeSafeFields())->handle($req, fn ($r) => response('ok'));
    return $req->input();
};
$b = fn ($s) => 'b64:' . base64_encode($s);
$r = $run(['title' => 'Plain', '_b64' => ['body' => $b("  hello <script>x</script>\n")]]);
$check('decodes, trims, removes the _b64 group', $r['body'] === 'hello <script>x</script>' && $r['title'] === 'Plain' && ! isset($r['_b64']));
$r = $run(['_b64' => ['translations' => ['pt' => ['title' => $b('Olá'), 'body' => $b('Corpo')]], 'title' => $b('EN')], 'translations' => ['es' => ['title' => 'Hola']]]);
$check('nested names are rebuilt next to existing plain ones', $r['translations']['pt']['body'] === 'Corpo' && $r['translations']['pt']['title'] === 'Olá' && $r['translations']['es']['title'] === 'Hola' && $r['title'] === 'EN');
$r = $run(['body' => 'orig', '_b64' => ['body' => 'b64:!!!not base64!!!']]);
$check('invalid base64 is ignored (original value kept)', $r['body'] === 'orig');
$r = $run(['_b64' => ['body' => base64_encode('no prefix')]]);
$check('values without the b64: prefix are ignored', ! isset($r['body']));
$r = $run(['_b64' => ['body' => 'b64:' . base64_encode("\xff\xfe\xfd")]]);
$check('bytes that are not UTF-8 are ignored', ! isset($r['body']));
$r = $run(['_b64' => ['excerpt' => $b("   ")]]);
$check('whitespace-only becomes null like Laravel does for empty fields', array_key_exists('excerpt', $r) && $r['excerpt'] === null);
$r = $run(['title' => 'X', 'body' => 'Y']);
$check('requests without _b64 pass through untouched', $r === ['title' => 'X', 'body' => 'Y']);
$r = $run(['_b64' => ['body' => $b('from json')], 'locales' => ['pt']], true);
$check('JSON requests (preview / AI calls) are decoded too', $r['body'] === 'from json' && $r['locales'] === ['pt'] && ! isset($r['_b64']));
$r = $run(['_b64' => 'not-an-array', 'a' => 1]);
$check('a malformed _b64 value is harmless', $r['a'] === 1);

echo "== saving your post through the real admin route\n";
$jar = [];
$send = function (string $method, string $path, array $data = []) use ($kernel, &$jar) {
    Cache::flush(); app('cookie')->flushQueuedCookies();
    $res = $kernel->handle(Request::create('https://franciscowebdev.com' . $path, $method, $data, $jar));
    app('cookie')->flushQueuedCookies();
    foreach ($res->headers->getCookies() as $c) { if ($c->isCleared()) unset($jar[$c->getName()]); else $jar[$c->getName()] = $c->getValue(); }
    return $res;
};
$tok = fn ($html) => preg_match('/name="_token" value="([^"]+)"/', $html, $m) ? $m[1] : '';
$send('POST', '/admin/login', ['_token' => $tok($send('GET', '/admin/login')->getContent()), 'password' => 'pw']);
$check('logged in', $send('GET', '/admin/posts')->getStatusCode() === 200);
$t = $tok($send('GET', '/admin/posts/create')->getContent());
$r = $send('POST', '/admin/posts', ['_token' => $t, 'status' => 'published', 'tags' => 'accessibility, wcag', 'ai_assisted' => '1',
    '_b64' => ['title' => $b('Making a project gallery accessible'), 'excerpt' => $b('Keyboard focus, state and a live region'), 'body' => $out['body']]]);
$saved = Post::first();
$check('encoded save creates the post', $saved !== null && str_contains((string) $r->headers->get('Location'), '/edit'), (string) $r->getStatusCode());
$check('stored body is your Markdown, identical (only outer whitespace trimmed)', $saved && $saved->body === trim($post), $saved ? substr($saved->body, 0, 60) : 'none');
$check('title and excerpt decoded', $saved->title === 'Making a project gallery accessible' && $saved->excerpt === 'Keyboard focus, state and a live region' && $saved->slug === 'making-a-project-gallery-accessible');
$check('published with tags', $saved->isPublished() && $saved->tags === ['accessibility', 'wcag']);

$page = $send('GET', '/admin/posts/' . $saved->id . '/edit')->getContent();
$check('edit page shows the body in the textarea (HTML escaped) and marks fields data-safe', str_contains($page, 'data-safe') && str_contains($page, 'aria-pressed=&quot;true&quot;') && ! str_contains($page, '<button' . "\n" . '  class="focus-visible'));
// saving it again from the edit form, this time with a Portuguese translation containing code too
$r = $send('POST', '/admin/posts/' . $saved->id, ['_token' => $tok($page), '_method' => 'PUT', 'status' => 'published', 'tags' => 'accessibility, wcag', 'ai_assisted' => '1',
    '_b64' => ['title' => $b('Making a project gallery accessible'), 'body' => $b($saved->body . "\n\n<script>alert(1)</script>"), 'translations' => ['pt' => ['title' => $b('Galeria acessível'), 'body' => $b("## Olá\n\n`document.getElementById('x')`")]]]]);
$saved->refresh();
$check('update through the encoded route works, translation saved', str_contains($saved->body, '<script>alert(1)</script>') && ($saved->translations['pt']['body'] ?? '') === "## Olá\n\n`document.getElementById('x')`" && $saved->translations['pt']['title'] === 'Galeria acessível');

echo "== how the post renders for visitors\n";
$h = $send('GET', '/blog/making-a-project-gallery-accessible')->getContent();
$check('headings, lists and code blocks render', substr_count($h, '<h2 id="') >= 6 && str_contains($h, '<li>Let users reach every card') && substr_count($h, '<pre><code') === 3);
$check('HTML examples inside code blocks are shown as text, not run', str_contains($h, '&lt;button') && str_contains($h, '&lt;div id=&quot;gallery-status&quot;') && ! str_contains($h, '<div id="gallery-status"'));
$check('the raw <script> pasted outside a code block is stripped', ! str_contains($h, '<script>alert(1)</script>') && ! str_contains($h, 'alert(1)</script>'));
$check('inline code, typographic quotes and template literals intact', str_contains($h, '<code>focus:outline-none</code>') && str_contains($h, 'isn’t finished') && str_contains($h, '${count}'));
$check('internal links stay on-site, [TODO] placeholders are visible for you to fill in', str_contains($h, 'href="/blog/why-web-accessibility-matters-for-everyone"') && str_contains($h, 'href="/#contact"') && str_contains($h, '[Add the contrast ratio you measured.]'));
$pt = $send('GET', '/pt/blog/making-a-project-gallery-accessible')->getContent();
$check('Portuguese version uses the translation', str_contains($pt, 'Galeria acessível') && str_contains($pt, 'document.getElementById'));

$send('GET', '/'); // keep session tidy
echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
