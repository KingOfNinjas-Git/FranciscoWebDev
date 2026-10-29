<?php
// "Web developer in <town>" pages: three languages, honest content, sitemap and footer links.
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
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path));
$towns = config('locations');
$slugs = array_keys($towns);

echo "== the data is complete and honest\n";
$complete = true; $problems = [];
foreach ($towns as $slug => $t) {
    foreach (['blurb', 'near', 'businesses'] as $field) foreach (['en', 'pt', 'es'] as $l) { if (empty($t[$field][$l])) { $complete = false; $problems[] = "$slug.$field.$l"; } }
    if (count($t['businesses']['en']) !== count($t['businesses']['pt']) || count($t['businesses']['en']) !== count($t['businesses']['es'])) { $complete = false; $problems[] = "$slug businesses count"; }
}
$check('every town has its text in English, Portuguese and Spanish', $complete, implode(', ', $problems));
$blurbs = array_map(fn ($t) => $t['blurb']['en'], $towns);
$check('no two towns share the same text (no copy-paste pages)', count(array_unique($blurbs)) === count($blurbs));
$check('there is a page for the town I work from and for Almada', in_array('seixal', $slugs, true) && in_array('almada', $slugs, true));

echo "== the pages\n";
foreach ($slugs as $slug) {
    $name = $towns[$slug]['name'];
    $en = $get("/web-developer/$slug"); $h = $en->getContent();
    $check("English $slug: 200, own title, own text, one h1", $en->getStatusCode() === 200 && str_contains($h, "<title>Web developer in $name | Francisco Cavaco</title>") && str_contains($h, e($towns[$slug]['blurb']['en'])) && substr_count($h, '<h1') === 1);
}
$h = $get('/web-developer/seixal')->getContent();
$check('canonical and the three-language hreflang', str_contains($h, '<link rel="canonical" href="https://franciscowebdev.com/web-developer/seixal">') && str_contains($h, 'hreflang="pt" href="https://franciscowebdev.com/pt/programador-web/seixal"') && str_contains($h, 'hreflang="es" href="https://franciscowebdev.com/es/desarrollador-web/seixal"'));
$check('structured data names the town and the real base, with breadcrumbs', str_contains($h, '"@type":"ProfessionalService"') && str_contains($h, '"areaServed":{"@type":"City","name":"Seixal"') && str_contains($h, '"addressLocality":"Seixal"') && str_contains($h, '"@type":"BreadcrumbList"'));
$check('it reuses the real services, process steps and questions', str_contains($h, 'Websites for businesses') && str_contains($h, 'How we work together') && str_contains($h, 'How much does a website cost?'));
$check('buttons lead to the contact form and WhatsApp, and to the other towns', str_contains($h, 'href="https://franciscowebdev.com#contact"') && str_contains($h, 'wa.me/') && str_contains($h, 'href="https://franciscowebdev.com/web-developer/almada"') && ! str_contains($h, 'href="https://franciscowebdev.com/web-developer/seixal" class="chip'));
$pt = $get('/pt/programador-web/almada'); $ph = $pt->getContent();
$check('Portuguese Almada: translated title, text and buttons', $pt->getStatusCode() === 200 && str_contains($ph, '<html lang="pt">') && str_contains($ph, 'Programador web em Almada') && str_contains($ph, e($towns['almada']['blurb']['pt'])) && str_contains($ph, 'Iniciar um projeto') && str_contains($ph, 'href="https://franciscowebdev.com/pt#contact"'));
$es = $get('/es/desarrollador-web/setubal'); $eh = $es->getContent();
$check('Spanish Setúbal: translated title and text', $es->getStatusCode() === 200 && str_contains($eh, '<html lang="es">') && str_contains($eh, 'Desarrollador web en Setúbal') && str_contains($eh, e($towns['setubal']['blurb']['es'])));
$check('an unknown town is a 404 in every language', $get('/web-developer/atlantis')->getStatusCode() === 404 && $get('/pt/programador-web/atlantis')->getStatusCode() === 404 && $get('/es/desarrollador-web/atlantis')->getStatusCode() === 404);
$check('the English address ignores a saved Portuguese cookie', str_contains($kernel->handle(Request::create('https://franciscowebdev.com/web-developer/seixal', 'GET', [], ['site_locale' => 'pt']))->getContent(), '<html lang="en">'));

echo "== found from the rest of the site\n";
$sm = $get('/sitemap.xml')->getContent();
$all = true; foreach ($slugs as $slug) { foreach (['/web-developer/', '/pt/programador-web/', '/es/desarrollador-web/'] as $prefix) { if (! str_contains($sm, "<loc>https://franciscowebdev.com$prefix$slug</loc>")) $all = false; } }
$check('the sitemap lists every town in three languages', $all);
$home = $get('/')->getContent(); $ptHome = $get('/pt')->getContent();
$check('the footer links to the towns, in the page language', str_contains($home, 'Areas I work in') && str_contains($home, 'href="https://franciscowebdev.com/web-developer/almada"') && str_contains($ptHome, 'Zonas onde trabalho') && str_contains($ptHome, 'href="https://franciscowebdev.com/pt/programador-web/almada"'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
