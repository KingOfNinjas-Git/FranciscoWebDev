<?php
// Light is the default look; a switch in the header lets a visitor choose a dark version, and the choice is remembered.
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

foreach (['/' => ['Switch to the light theme', 'Switch to the dark theme'], '/pt' => ['Mudar para o tema claro', 'Mudar para o tema escuro'], '/es/blog' => ['Cambiar al tema claro', 'Cambiar al tema oscuro'], '/blog' => ['Switch to the light theme', 'Switch to the dark theme']] as $path => [$toLight, $toDark]) {
    $h = $get($path);
    $check("$path: a theme switch that offers the dark theme first, with both labels ready for the script", str_contains($h, 'id="theme-toggle"') && str_contains($h, 'aria-label="' . $toDark . '"') && str_contains($h, 'data-l-to-light="' . $toLight . '"') && str_contains($h, 'data-l-to-dark="' . $toDark . '"'));
    $check("$path: a saved dark choice is applied before the page paints (inline script, with the security nonce)", (bool) preg_match('/<script nonce="[^"]+">document\.documentElement\.classList\.add\(\'js\'\);try\{if\(localStorage\.getItem\(\'theme\'\)===\'dark\'\)document\.documentElement\.dataset\.theme=\'dark\'\}catch\(e\)\{\}<\/script>/', $h));
    $check("$path: light is the default (no theme attribute in the markup)", ! str_contains($h, 'data-theme='));
    $check("$path: no animated background", ! str_contains($h, 'bg-fx') && ! str_contains($h, 'bg-orb'));
}

$css = file_get_contents('resources/css/app.css');
$check('the light palette is defined once, in app.css', str_contains($css, '--page-bg: #eef3f1') && str_contains($css, '--red: #c4401c') && str_contains($css, '--color-white: #0b2623'));
$site = file_get_contents('resources/css/site.css');
$check('the stylesheet has a dark theme that flips the colour scale', str_contains($site, ':root[data-theme="dark"]') && str_contains($site, '--page-bg: #0a1f1c') && str_contains($site, '--color-white: #e6f0ed') && str_contains($site, '--card: #12302b'));
$check('filled green parts keep the brand green in both themes', str_contains($site, '--brand: #0e5a52') && ! str_contains($site, 'background: var(--green)'));
$check('the dark theme does not follow the system setting (light stays the default)', ! str_contains($site, 'prefers-color-scheme'));
$js = file_get_contents('resources/js/site.js');
$check('the script remembers the choice and tolerates blocked storage', str_contains($js, "localStorage.setItem('theme'") && str_contains($js, 'catch (e)'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
