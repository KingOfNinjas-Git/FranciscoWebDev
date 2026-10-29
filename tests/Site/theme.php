<?php
// Light theme: the switch, the saved choice applied before first paint, and the colours for both themes.
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
    $check("$path: a theme switch with both labels, ready for the script", str_contains($h, 'id="theme-toggle"') && str_contains($h, 'aria-label="' . $toLight . '"') && str_contains($h, 'data-l-to-light="' . $toLight . '"') && str_contains($h, 'data-l-to-dark="' . $toDark . '"'));
    $check("$path: the saved choice is applied before the page paints (inline script, with the security nonce)", (bool) preg_match('/<script nonce="[^"]+">document\.documentElement\.classList\.add\(\'js\'\);try\{var t=localStorage\.getItem\(\'theme\'\);if\(t===\'light\'\|\|t===\'dark\'\)document\.documentElement\.dataset\.theme=t\}catch\(e\)\{\}<\/script>/', $h));
}
$check('the page still defaults to the dark theme (no theme attribute in the markup)', ! str_contains($get('/'), 'data-theme='));

$css = file_get_contents('resources/css/site.css');
$check('the stylesheet has a light theme that flips the colour scale', str_contains($css, ':root[data-theme="light"]') && str_contains($css, '--color-white: #0f172a') && str_contains($css, '--page-bg: #f3f6fb'));
$js = file_get_contents('resources/js/site.js');
$check('the script remembers the choice and tolerates blocked storage', str_contains($js, "localStorage.setItem('theme'") && str_contains($js, 'catch (e)'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
