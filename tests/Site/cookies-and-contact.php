<?php
// Lazy sessions: public pages are cookie-free and write no session rows; contact form + admin still work.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'database', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'ADMIN_PASSWORD' => 'correct-horse-battery', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => '', 'SESSION_SECURE_COOKIE' => 'false', 'SESSION_COOKIE' => 'francisco-cavaco-session'] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\{DB, Cache, Mail}; use App\Models\Post;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> $extra" : '') . "\n"; $ok ? $pass++ : $fail++; };

// a tiny browser: keeps cookies between requests
$jar = [];
$send = function (string $method, string $path, array $data = [], array $headers = [], bool $useJar = true) use ($kernel, &$jar) {
    $server = [];
    foreach ($headers as $k => $v) $server['HTTP_' . strtoupper(str_replace('-', '_', $k))] = $v;
    // every real request is a fresh PHP process: reset what this single test process would otherwise carry over
    app('cookie')->flushQueuedCookies();
    if (! $useJar) app('session')->driver()->flush();
    $req = Request::create('https://franciscowebdev.com' . $path, $method, $data, $useJar ? $jar : [], [], $server);
    $res = $kernel->handle($req);
    app('cookie')->flushQueuedCookies();
    if ($useJar) foreach ($res->headers->getCookies() as $c) { if ($c->isCleared()) unset($jar[$c->getName()]); else $jar[$c->getName()] = $c->getValue(); }
    return $res;
};
$tokenOf = fn ($html) => preg_match('/name="_token" value="([^"]+)"/', $html, $m) ? $m[1] : '';
$agedToken = function (int $age) { $t = time() - $age; $ref = new ReflectionMethod(App\Support\FormToken::class, 'sign'); return $t . '.' . $ref->invoke(null, $t); };
$mailCount = fn () => count(app('mail.manager')->mailer('array')->getSymfonyTransport()->messages());
$sessions = fn () => DB::table('sessions')->count();
$cookieNames = fn ($res) => array_map(fn ($c) => $c->getName(), $res->headers->getCookies());

echo "== public pages: no cookies, no session rows\n";
foreach (['/', '/pt', '/es', '/blog', '/pt/blog', '/blog/feed.xml', '/sitemap.xml', '/nope-404', '/privacy'] as $u) {
    $r = $send('GET', $u, [], [], false);
    $check("GET $u -> no Set-Cookie header", $r->headers->get('Set-Cookie') === null && $cookieNames($r) === [], json_encode($cookieNames($r)) . ' status ' . $r->getStatusCode());
}
$check('no session rows after all those visits', $sessions() === 0, (string) $sessions());
$home = $send('GET', '/', [], [], false)->getContent();
$check('home renders with signed form token + language, no session csrf token', preg_match('/name="_ft" value="\d+\.[a-f0-9]{64}"/', $home) === 1 && str_contains($home, 'name="_locale" value="en"') && !str_contains($home, 'name="_token"'));
$check('/lang/pt (explicit switch) still sets the language cookie', in_array('site_locale', $cookieNames($send('GET', '/lang/pt', [], [], false)), true));
$check('/pt visit alone sets no cookie', $cookieNames($send('GET', '/pt', [], [], false)) === []);

echo "== contact form without a session\n";
$good = ['name' => 'Ana', 'email' => 'ana@example.com', 'project_type' => 'landing', 'description' => 'I need a landing page for my shop.', 'features' => ['seo'], 'budget' => 'small', '_locale' => 'en'];
$r = $send('POST', '/contact', $good, [], false);
$check('no token -> ignored quietly, no mail, no cookie', $r->getStatusCode() === 302 && $mailCount() === 0 && $cookieNames($r) === [], (string) $mailCount());
Cache::flush();
$r = $send('POST', '/contact', $good + ['_ft' => '123456789.' . str_repeat('a', 64)], [], false);
$check('forged token -> ignored, no mail', $mailCount() === 0);
Cache::flush();
$r = $send('POST', '/contact', $good + ['_ft' => App\Support\FormToken::make()], [], false);
$check('instant submit (bot speed) -> ignored, no mail', $mailCount() === 0);
Cache::flush();
$r = $send('POST', '/contact', $good + ['_ft' => $agedToken(10), 'website' => 'http://spam'], [], false);
$check('honeypot filled -> ignored, no mail', $mailCount() === 0);
Cache::flush();

$r = $send('POST', '/contact', $good + ['_ft' => $agedToken(10)]);
$check('valid token -> two mails (to me + confirmation), redirect to #contact', $r->getStatusCode() === 302 && str_contains((string) $r->headers->get('Location'), '#contact') && $mailCount() === 2, "mails=" . $mailCount() . ' loc=' . $r->headers->get('Location'));
$check('only now a session cookie + row exist', in_array('francisco-cavaco-session', $cookieNames($r), true) && $sessions() === 1);
$h = $send('GET', '/')->getContent();
$check('follow-up page shows the success message (flash works)', str_contains($h, 'Your brief was sent'));
$h = $send('GET', '/')->getContent();
$check('message shown only once', !str_contains($h, 'Your brief was sent'));

$jar = []; Cache::flush();
$r = $send('POST', '/contact', ['_ft' => $agedToken(10), '_locale' => 'pt', 'name' => '', 'email' => 'x']);
$check('validation failure redirects to the Portuguese page', str_contains((string) $r->headers->get('Location'), '/pt#contact'), (string) $r->headers->get('Location'));
$h = $send('GET', '/pt')->getContent();
$check('errors shown in Portuguese with aria wiring', str_contains($h, 'obrigat') && str_contains($h, 'aria-invalid="true"') && str_contains($h, 'href="#c-name"'));
$check('no mail for invalid input', $mailCount() === 2);

$jar = []; Cache::flush();
$r = $send('POST', '/contact', $good + ['_ft' => $agedToken(7300)]);
$h = $send('GET', '/')->getContent();
$check('expired token -> friendly message, input kept', str_contains($h, 'open for too long') && str_contains($h, 'value="Ana"') && $mailCount() === 2);

echo "== admin still protected\n";
$jar = []; Cache::flush();
$r = $send('GET', '/admin/login');
$check('login page starts a session (needs it for CSRF)', in_array('francisco-cavaco-session', $cookieNames($r), true) && in_array('XSRF-TOKEN', $cookieNames($r), true));
$token = $tokenOf($r->getContent());
$check('login form has a CSRF token', $token !== '');
$r = $send('POST', '/admin/login', ['password' => 'correct-horse-battery']);
$check('login POST without CSRF token -> 419', $r->getStatusCode() === 419, (string) $r->getStatusCode());
$r = $send('POST', '/admin/login', ['_token' => $token, 'password' => 'wrong']);
$check('wrong password -> back to login', $r->getStatusCode() === 302 && str_contains((string) $r->headers->get('Location'), 'admin/login'));
$token = $tokenOf($send('GET', '/admin/login')->getContent());
$r = $send('POST', '/admin/login', ['_token' => $token, 'password' => 'correct-horse-battery']);
$check('right password + token -> admin', $r->getStatusCode() === 302 && str_contains((string) $r->headers->get('Location'), '/admin/projects'), (string) $r->headers->get('Location'));
$check('admin pages work', $send('GET', '/admin/posts')->getStatusCode() === 200 && $send('GET', '/admin/projects')->getStatusCode() === 200);
$xsrf = urldecode(\Illuminate\Support\Str::of($jar['XSRF-TOKEN'] ?? '')->toString());
$decrypt = app('encrypter');
$xsrfPlain = $jar['XSRF-TOKEN'] ?? '';
$r = $send('POST', '/admin/posts/preview', ['body' => '## hi']);
$check('admin POST without CSRF -> 419', $r->getStatusCode() === 419, (string) $r->getStatusCode());
$token = $tokenOf($send('GET', '/admin/posts/create')->getContent());
$r = $send('POST', '/admin/posts/preview', ['_token' => $token, 'body' => '## hi']);
$check('admin POST with CSRF token works', $r->getStatusCode() === 200 && str_contains((string) (json_decode($r->getContent(), true)['html'] ?? ''), '<h2>hi</h2>'), $r->getContent());

Post::create(['title' => 'Secret draft', 'slug' => 'secret-draft', 'body' => 'x', 'status' => 'draft']);
$check('logged-in admin sees the Admin link on public pages', str_contains($send('GET', '/')->getContent(), 'admin/projects'));
$check('logged-in admin can preview a draft', $send('GET', '/blog/secret-draft')->getStatusCode() === 200);
$visitor = $send('GET', '/blog/secret-draft', [], [], false);
$check('visitor (no cookie) cannot see the draft', $visitor->getStatusCode() === 404);
$check('visitor sees no Admin link', !str_contains($send('GET', '/', [], [], false)->getContent(), 'admin/projects'));
$r = $send('POST', '/admin/logout', [], [], false);
$check('POST to admin with no cookies at all -> 419, not a crash', $r->getStatusCode() === 419, (string) $r->getStatusCode());
$token = $tokenOf($send('GET', '/admin/posts/create')->getContent());
$send('POST', '/admin/logout', ['_token' => $token]);
$check('after logout the admin pages are closed again', $send('GET', '/admin/posts')->getStatusCode() === 302);

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
