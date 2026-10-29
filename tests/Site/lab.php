<?php
// The Lab: every live demo, with an online / offline light.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);


use Illuminate\Http\Request; use Illuminate\Support\Facades\{Cache, Http}; use App\Models\Project; use App\Services\DemoStatus;
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path));
$fake = function (array $stubs) { Http::swap(new Illuminate\Http\Client\Factory()); Http::fake($stubs); };

Project::create(['title' => 'Alpha Demo', 'slug' => 'alpha-demo', 'description' => 'The first demo', 'technologies' => ['Laravel', 'Tailwind'], 'live_url' => 'https://alpha.example', 'translations' => ['pt' => ['title' => 'Demo Alfa']]]);
Project::create(['title' => 'Beta Demo', 'slug' => 'beta-demo', 'description' => 'The second demo', 'live_url' => 'https://beta.example']);
Project::create(['title' => 'No Demo', 'slug' => 'no-demo', 'description' => 'Only a case study']);

echo "== the status check\n";
$fake(['*alpha.example*' => Http::response('ok', 200), '*beta.example*' => Http::response('boom', 500)]);
$s = DemoStatus::checkMany(['https://alpha.example', 'https://beta.example', 'https://alpha.example']);
$check('an answering demo is up, a failing one is down, duplicates are asked once', $s['https://alpha.example']['up'] === true && $s['https://beta.example']['up'] === false && count($s) === 2 && count(Http::recorded()) === 2, json_encode($s));
$check('a down demo has no response time, an up one has', $s['https://beta.example']['ms'] === null && is_int($s['https://alpha.example']['ms']));
$fake(['*' => Http::response('changed', 500)]);
$again = DemoStatus::checkMany(['https://alpha.example']);
$check('the answer is remembered for ten minutes (no new request)', $again['https://alpha.example']['up'] === true && count(Http::recorded()) === 0);
Cache::flush();
$fake(['*' => fn () => throw new Illuminate\Http\Client\ConnectionException('timeout')]);
$down = DemoStatus::checkMany(['https://gamma.example']);
$check('a timeout is "down", never an error page', $down['https://gamma.example']['up'] === false);

echo "== the page\n";
Cache::flush();
$fake(['*alpha.example*' => Http::response('ok', 200), '*beta.example*' => Http::response('boom', 500)]);
$h = $get('/lab')->getContent();
$check('English page lists the demos that have a live address, not the others', str_contains($h, 'Live demos you can click through') && str_contains($h, 'Alpha Demo') && str_contains($h, 'Beta Demo') && ! str_contains($h, 'No Demo'));
$check('each demo shows its light: online for one, not answering for the other', str_contains($h, 'Online') && str_contains($h, 'Not answering right now'));
$check('buttons open the demo in a new tab and link to the case study', str_contains($h, 'href="https://alpha.example"') && str_contains($h, 'target="_blank" rel="noopener noreferrer"') && str_contains($h, 'https://franciscowebdev.com/projects/alpha-demo'));
$check('title, canonical and hreflang', str_contains($h, '<title>Lab: live demos | Francisco Cavaco</title>') && str_contains($h, '<link rel="canonical" href="https://franciscowebdev.com/lab">') && str_contains($h, 'hreflang="pt" href="https://franciscowebdev.com/pt/lab"'));
$pt = $get('/pt/lab')->getContent(); $es = $get('/es/lab')->getContent();
$check('Portuguese: translated page, translated title of the demo, link to the Portuguese case study', str_contains($pt, '<html lang="pt">') && str_contains($pt, 'Demos ao vivo para experimentar') && str_contains($pt, 'Demo Alfa') && str_contains($pt, 'Abrir a demo ao vivo') && str_contains($pt, 'https://franciscowebdev.com/pt/projects/alpha-demo'));
$check('Spanish page is in Spanish', str_contains($es, '<html lang="es">') && str_contains($es, 'Demos en vivo para probar') && str_contains($es, 'En línea'));
$check('"Checked just now" on a fresh check', str_contains($h, 'Checked just now'));
$check('the footer links to the Lab, in the page language', str_contains($get('/')->getContent(), 'href="https://franciscowebdev.com/lab"') && str_contains($get('/pt/blog')->getContent(), 'href="https://franciscowebdev.com/pt/lab"'));
$sm = $get('/sitemap.xml')->getContent();
$check('the sitemap lists the Lab in three languages', str_contains($sm, '<loc>https://franciscowebdev.com/lab</loc>') && str_contains($sm, '<loc>https://franciscowebdev.com/pt/lab</loc>') && str_contains($sm, '<loc>https://franciscowebdev.com/es/lab</loc>'));

Project::query()->delete(); Cache::flush();
$fake([]);
$check('with no demos the page says so politely', str_contains($get('/lab')->getContent(), 'No live demos to show yet.'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
