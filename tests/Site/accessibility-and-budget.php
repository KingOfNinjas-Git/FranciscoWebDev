<?php
// Plan 16: the accessibility statement page, and a size budget that catches a page getting heavier by accident.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use App\Models\{Post, Project};
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path));

echo "== accessibility statement\n";
foreach (['/accessibility' => ['en', 'Accessibility statement', 'WCAG 2.2'], '/pt/accessibility' => ['pt', 'Declaração de acessibilidade', 'WCAG 2.2'], '/es/accessibility' => ['es', 'Declaración de accesibilidad', 'WCAG 2.2']] as $path => [$loc, $title, $needle]) {
    $r = $get($path); $h = $r->getContent();
    $check("$path: page in the right language with its own title", $r->getStatusCode() === 200 && str_contains($h, "<html lang=\"$loc\">") && str_contains($h, "<title>$title | Francisco Cavaco</title>") && str_contains($h, $needle));
    $check("$loc: four sections, a list of what is checked, and a way to report a barrier (your email)", substr_count($h, 'aria-labelledby="a11y-') === 4 && substr_count($h, 'marker:text-sky-400') >= 1 && str_contains($h, 'mailto:' . config('portfolio.contact_email')));
    $check("$loc: canonical and links to the other two languages", str_contains($h, '<link rel="canonical" href="https://franciscowebdev.com' . ($loc === 'en' ? '' : "/$loc") . '/accessibility">') && substr_count($h, 'hreflang="') >= 4);
    $check("$loc: no placeholder text left", ! str_contains($h, ':email') && ! str_contains($h, 'site.a11y'));
}
$home = $get('/')->getContent();
$check('footer links to it from every page, in the page language', str_contains($home, 'href="https://franciscowebdev.com/accessibility"') && str_contains($get('/pt')->getContent(), 'href="https://franciscowebdev.com/pt/accessibility"') && str_contains($get('/es/blog')->getContent(), 'href="https://franciscowebdev.com/es/accessibility"'));
$xml = $get('/sitemap.xml')->getContent();
$check('in the sitemap in three languages, with alternates', @simplexml_load_string($xml) !== false && substr_count($xml, '/accessibility</loc>') === 3 && str_contains($xml, 'hreflang="pt" href="https://franciscowebdev.com/pt/accessibility"'));
$check('the three languages have the same sections', collect(['en', 'pt', 'es'])->every(fn ($l) => count(trans('site.a11y.sections', [], $l)) === 4 && trans('site.footer.accessibility', [], $l) !== 'site.footer.accessibility'));

echo "== size budget (what a visitor downloads for the home page)\n";
Project::create(['title' => 'Alpha', 'slug' => 'alpha', 'description' => 'D']);
$html = $get('/')->getContent();
preg_match_all('~(?:href|src)="https://franciscowebdev\.com(/build/assets/[^"]+\.(?:css|js))"~', $html, $m);
$assets = array_unique($m[1]);
$bytes = fn (string $kind) => collect($assets)->filter(fn ($a) => str_ends_with($a, ".$kind"))->sum(fn ($a) => is_file(public_path($a)) ? filesize(public_path($a)) : 0);
$gz = fn (string $kind) => collect($assets)->filter(fn ($a) => str_ends_with($a, ".$kind"))->sum(fn ($a) => is_file(public_path($a)) ? strlen(gzencode(file_get_contents(public_path($a)), 6)) : 0);
$check('the page loads its scripts and styles from the build (found ' . count($assets) . ' files)', count($assets) >= 2);
$check('JavaScript for the home page stays small: under 40 KB compressed (now ' . round($gz('js') / 1024, 1) . ' KB)', $gz('js') > 0 && $gz('js') <= 40 * 1024);
$check('CSS stays small: under 30 KB compressed (now ' . round($gz('css') / 1024, 1) . ' KB)', $gz('css') > 0 && $gz('css') <= 30 * 1024);
$check('the page itself (HTML) stays under 80 KB uncompressed (now ' . round(strlen($html) / 1024, 1) . ' KB)', strlen($html) <= 80 * 1024);
$check('the home page does not load the admin script', ! str_contains($html, 'admin-'));
preg_match_all('~(?:src|href)="(https?://[^"]+)"~', $html, $links);
$external = collect($links[1])->filter(fn ($u) => ! str_starts_with($u, 'https://franciscowebdev.com'))->map(fn ($u) => parse_url($u, PHP_URL_HOST))->unique()->values();
$loaded = collect($links[0])->filter(fn ($full, $i) => preg_match('/<(script|link[^>]*rel="(?:stylesheet|preload|icon)")/', substr($html, max(0, strpos($html, $full) - 80), 80 + strlen($full)) ) === 1);
$check('no third-party scripts, fonts or trackers are loaded (only normal links go elsewhere)', ! preg_match('~<script[^>]+src="https?://(?!franciscowebdev\.com)~', $html) && ! preg_match('~<link[^>]+rel="stylesheet"[^>]+href="https?://(?!franciscowebdev\.com)~', $html), $external->implode(', '));
$check('every build file the page asks for exists (nothing 404s)', collect($assets)->every(fn ($a) => is_file(public_path($a))));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
