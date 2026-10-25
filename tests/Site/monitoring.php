<?php
// Plan 4: health check, error alert emails, the scheduled self-check.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\{Artisan, Cache, DB, Http, Route};
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path));
$mails = fn () => app('mail.manager')->mailer('array')->getSymfonyTransport()->messages();

echo "== /health\n";
$r = $get('/health'); $j = json_decode($r->getContent(), true);
$check('200 and ok:true when everything works', $r->getStatusCode() === 200 && $j['ok'] === true && $j['database'] === true && $j['storage'] === true, $r->getContent());
$check('never cached, and reveals nothing else', $r->headers->get('Cache-Control') === 'no-store, private' || str_contains((string) $r->headers->get('Cache-Control'), 'no-store'));
$check('/up (Laravel built-in) still answers', $get('/up')->getStatusCode() === 200);

echo "== error alert email\n";
Route::get('/boom', fn () => throw new RuntimeException('database exploded for the demo'));
$before = count($mails());
$r = $get('/boom?secret=hunter2&email=a@b.c');
$check('visitor gets the themed 500 page', $r->getStatusCode() === 500);
$all = $mails();
$check('one alert email was sent to the owner', count($all) === $before + 1 && $all[$before]->getOriginalMessage()->getTo()[0]->getAddress() === config('portfolio.contact_email'));
$msg = $all[$before]->getOriginalMessage();
$body = $msg->getTextBody();
$check('subject names the error and the page', str_contains($msg->getSubject(), 'RuntimeException') && str_contains($msg->getSubject(), '/boom'), $msg->getSubject());
$check('body explains what happened', str_contains($body, 'database exploded for the demo') && str_contains($body, 'Page: /boom') && str_contains($body, 'laravel.log'));
$check('query string and form data are NOT in the email', ! str_contains($body, 'hunter2') && ! str_contains($body . $msg->getSubject(), 'a@b.c'));
$get('/boom'); $get('/boom');
$check('repeated failures do not flood the inbox (one email per 10 minutes)', count($mails()) === $before + 1);
Cache::flush();
$get('/boom');
$check('after the cool-down another alert may go out', count($mails()) === $before + 2);
$count = count($mails());
$get('/nope-404'); $get('/blog/missing-post-slug');
$check('404 pages never trigger an alert', count($mails()) === $count);
config(['app.env' => 'local']); $app['env'] = 'local'; Cache::flush();
$get('/boom');
$check('no alerts outside production (local development stays quiet)', count($mails()) === $count);
$app['env'] = 'production'; config(['app.env' => 'production']);

echo "== health when the database is down\n";
Cache::flush();
config(['database.connections.broken' => ['driver' => 'sqlite', 'database' => '/nonexistent/dir/x.sqlite']]);
DB::purge(); config(['database.default' => 'broken']);
$r = $get('/health'); $j = json_decode($r->getContent(), true);
$check('503 and database:false', $r->getStatusCode() === 503 && $j['ok'] === false && $j['database'] === false, $r->getContent());
config(['database.default' => 'sqlite']); DB::purge();

echo "== scheduled self-check (site:check)\n";
$okPages = fn () => Http::fake([
    '*/sitemap.xml' => Http::response('<urlset></urlset>', 200),
    '*/health' => Http::response('{"ok":true}', 200),
    '*' => Http::response('<html>Francisco Cavaco Blog</html>', 200),
]);
Cache::flush(); $okPages();
$n = count($mails());
$code = Artisan::call('site:check', ['--url' => 'http://example.test']);
$check('all fine -> exit 0 and no email', $code === 0 && count($mails()) === $n && str_contains(Artisan::output(), 'fine'));
Http::swap(new Illuminate\Http\Client\Factory());
Http::fake(['*/blog' => Http::response('boom', 500), '*/health' => Http::response('{"ok":true}', 200), '*/sitemap.xml' => Http::response('<urlset>', 200), '*' => Http::response('Francisco Cavaco Blog', 200)]);
$code = Artisan::call('site:check', ['--url' => 'http://example.test']);
$msg = $mails()[$n]->getOriginalMessage() ?? null;
$check('a broken page -> exit 1 and one email naming it', $code === 1 && count($mails()) === $n + 1 && str_contains($msg->getTextBody(), '/blog answered HTTP 500') && str_contains($msg->getSubject(), '1 problem'), Artisan::output());
Artisan::call('site:check', ['--url' => 'http://example.test']);
$check('still broken a minute later -> no second email (one per hour)', count($mails()) === $n + 1);
Cache::flush(); Http::swap(new Illuminate\Http\Client\Factory());
Http::fake(['*/health' => Http::response('{"ok":true}', 200), '*/sitemap.xml' => Http::response('<urlset>', 200), '*' => Http::response('<html>Something else entirely</html>', 200)]);
$code = Artisan::call('site:check', ['--url' => 'http://example.test']);
$check('a page that loads but is wrong (missing expected text) is reported', $code === 1 && str_contains(Artisan::output(), 'does not look right'), Artisan::output());
Cache::flush(); Http::swap(new Illuminate\Http\Client\Factory());
Http::fake(fn () => throw new Illuminate\Http\Client\ConnectionException('timeout'));
$code = Artisan::call('site:check', ['--url' => 'http://example.test']);
$check('server not answering at all is reported', $code === 1 && str_contains(Artisan::output(), 'could not be reached'));
$schedule = app(Illuminate\Console\Scheduling\Schedule::class);
$check('the check is scheduled every 15 minutes', collect($schedule->events())->contains(fn ($e) => str_contains($e->command, 'site:check') && $e->expression === '*/15 * * * *'));

echo "== repository safety net\n";
$ci = file_get_contents('.github/workflows/tests.yml');
$check('GitHub runs the tests on every push to main', str_contains($ci, 'branches: [main]') && str_contains($ci, 'vendor/bin/pest') && str_contains($ci, 'npm run build'));
$sh = file_get_contents('scripts/deploy.sh');
$check('deploy script backs up first, never touches .env, and verifies afterwards', strpos($sh, 'backup.sh') < strpos($sh, 'git archive') && ! preg_match('/rm .*\.env|>\s*\.env|cp .* \.env\b/', $sh) && str_contains($sh, 'differs from GitHub') && str_contains($sh, '/health'));
$check('deploy script asks before changing the server unless --yes', str_contains($sh, 'read -r -p') && str_contains($sh, '--yes'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
