<?php
// One palette: the theme switch is gone, the colours come from the variables in app.css and the page has no theme attribute or saved choice.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request;
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path))->getContent();

foreach (['/', '/pt', '/es/blog', '/blog'] as $path) {
    $h = $get($path);
    $check("$path: no theme switch and no saved-theme script", ! str_contains($h, 'theme-toggle') && ! str_contains($h, "localStorage.getItem('theme')") && ! str_contains($h, 'data-theme='));
    $check("$path: no animated background", ! str_contains($h, 'bg-fx') && ! str_contains($h, 'bg-orb'));
    $check("$path: browser bar colour matches the page", str_contains($h, '<meta name="theme-color" content="#eef3f1">'));
}

$css = file_get_contents('resources/css/app.css');
$check('the palette is defined once, in app.css', str_contains($css, '--page-bg: #eef3f1') && str_contains($css, '--red: #c4401c') && str_contains($css, '--color-white: #0b2623'));
$site = file_get_contents('resources/css/site.css');
$check('the stylesheet has no dark theme left', ! str_contains($site, 'data-theme') && ! str_contains($site, 'neon-b'));
$js = file_get_contents('resources/js/site.js');
$check('the script has no theme code', ! str_contains($js, 'initTheme') && ! str_contains($js, "localStorage.setItem('theme'"));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
