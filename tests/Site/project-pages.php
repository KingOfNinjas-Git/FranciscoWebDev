<?php
// Plan 3: real project pages.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use App\Models\{Post, Project, ProjectImage}; use App\Services\ImageVariants;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path));

$dir = public_path('imgs/_proj_test'); @mkdir($dir, 0755, true); $R = 'imgs/_proj_test/';
foreach (['a', 'b'] as $n) { $im = imagecreatetruecolor(1600, 1000); imagefilledrectangle($im, 0, 0, 800, 500, imagecolorallocate($im, 30, 90, 200)); imagewebp($im, "$dir/$n.webp", 80); ImageVariants::make($R . "$n.webp"); }

$p = Project::create(['title' => 'Alpha', 'slug' => 'alpha', 'description' => 'English desc', 'notes' => "## Goal\nBuild **it**.\n\n<script>alert(1)</script>", 'technologies' => ['Laravel', 'Tailwind'], 'live_url' => 'https://a.example', 'repo_url' => 'https://github.com/x/a',
    'translations' => ['pt' => ['title' => 'Alfa', 'description' => 'Descricao PT', 'notes' => "## Objetivo\nConstruir."], 'es' => ['title' => 'Alfa ES', 'description' => 'Descripcion ES']]]);
ProjectImage::create(['project_id' => $p->id, 'path' => $R . 'a.webp', 'position' => 0]); ProjectImage::create(['project_id' => $p->id, 'path' => $R . 'b.webp', 'position' => 1]);
Project::create(['title' => 'Beta', 'slug' => 'beta', 'description' => 'Second']);
Post::create(['title' => 'Laravel tips', 'slug' => 'laravel-tips', 'body' => 'b', 'tags' => ['laravel'], 'status' => 'published', 'published_at' => now()]);
Post::create(['title' => 'Unrelated', 'slug' => 'unrelated', 'body' => 'b', 'tags' => ['cooking'], 'status' => 'published', 'published_at' => now()]);

echo "== routes\n";
$en = $get('/projects/alpha'); $html = $en->getContent();
$check('English page 200 and in English', $en->getStatusCode() === 200 && str_contains($html, '<html lang="en">') && str_contains($html, '<h1') && str_contains($html, 'Alpha') && str_contains($html, 'English desc'), substr($html, 0, 300));
$pt = $get('/pt/projects/alpha'); $ph = $pt->getContent();
$check('Portuguese page uses the translated text and UI', $pt->getStatusCode() === 200 && str_contains($ph, '<html lang="pt">') && str_contains($ph, 'Alfa') && str_contains($ph, 'Descricao PT') && str_contains($ph, 'Objetivo') && str_contains($ph, 'Caso de estudo'));
$es = $get('/es/projects/alpha'); $eh = $es->getContent();
$check('Spanish page: translated title, English notes fall back', $es->getStatusCode() === 200 && str_contains($eh, 'Alfa ES') && str_contains($eh, 'Descripcion ES') && str_contains($eh, 'Caso de estudio'));
$check('unknown slug is a 404 in all languages', $get('/projects/nope')->getStatusCode() === 404 && $get('/pt/projects/nope')->getStatusCode() === 404 && $get('/es/projects/nope')->getStatusCode() === 404);
$check('/projects still goes to the section', $get('/projects')->isRedirect() && str_contains($get('/projects')->headers->get('Location'), '#projects'));
$check('English page ignores a saved Portuguese cookie', str_contains($kernel->handle(Request::create('https://franciscowebdev.com/projects/alpha', 'GET', [], ['site_locale' => 'pt']))->getContent(), '<html lang="en">'));

echo "== SEO and content\n";
$check('title, canonical and hreflang for the three languages', str_contains($html, '<title>Alpha | Francisco Cavaco</title>') && str_contains($html, '<link rel="canonical" href="https://franciscowebdev.com/projects/alpha">') && str_contains($html, 'hreflang="pt" href="https://franciscowebdev.com/pt/projects/alpha"') && str_contains($html, 'hreflang="es" href="https://franciscowebdev.com/es/projects/alpha"') && str_contains($ph, '<link rel="canonical" href="https://franciscowebdev.com/pt/projects/alpha">'));
$check('meta description comes from the description', str_contains($html, '<meta name="description" content="English desc">'));
$check('share image is the first screenshot', str_contains($html, 'property="og:image" content="' . asset($R . 'a.webp') . '"'));
preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m); $ld = array_map(fn ($j) => json_decode($j, true), $m[1]);
$check('structured data: CreativeWork + BreadcrumbList (3 items), valid JSON', count($ld) === 2 && $ld[0]['@type'] === 'CreativeWork' && $ld[0]['name'] === 'Alpha' && $ld[0]['keywords'] === 'Laravel, Tailwind' && $ld[1]['@type'] === 'BreadcrumbList' && count($ld[1]['itemListElement']) === 3, json_encode($ld));
$check('notes render as Markdown and raw HTML is stripped', str_contains($html, '<h2>Goal</h2>') && str_contains($html, '<strong>it</strong>') && ! str_contains($html, '<script>alert'));
$check('tags, live and repo buttons', str_contains($html, 'Laravel') && str_contains($html, 'href="https://a.example"') && str_contains($html, 'href="https://github.com/x/a"') && str_contains($html, 'rel="noopener noreferrer"'));
$check('first screenshot is real HTML: srcset, size, eager', (bool) preg_match('/<img [^>]*a\.webp[^>]*fetchpriority="high"/', $html) && str_contains($html, 'srcset=') && str_contains($html, 'width="1600"'));
$check('gallery JSON present for 2 images, not for 0', str_contains($html, 'id="project-gallery-data"') && ! str_contains($get('/projects/beta')->getContent(), 'id="project-gallery-data"'));
$check('previous / next project links', str_contains($html, '/projects/beta') && str_contains($get('/projects/beta')->getContent(), '/projects/alpha'));
$check('related posts share a tag with the project', str_contains($html, 'Laravel tips') && ! str_contains($html, 'Unrelated'));
$check('call to action links to the contact section', str_contains($html, '#contact'));
$check('project without images or notes renders', $get('/projects/beta')->getStatusCode() === 200);

echo "== home and sitemap\n";
$home = $get('/')->getContent(); $php = $get('/pt')->getContent();
$check('home cards are real links to the project pages', str_contains($home, 'href="https://franciscowebdev.com/projects/alpha"') && str_contains($php, 'href="https://franciscowebdev.com/pt/projects/alpha"') && str_contains($home, 'data-project-slug="alpha"'));
$check('the pop-up and the embedded JSON are gone', ! str_contains($home, 'project-modal') && ! str_contains($home, 'projects-data') && ! str_contains($home, 'data-project="'));
$sm = $get('/sitemap.xml')->getContent();
$check('sitemap lists each project in 3 languages with alternates', substr_count($sm, '/projects/alpha</loc>') === 3 && str_contains($sm, '<loc>https://franciscowebdev.com/pt/projects/beta</loc>') && str_contains($sm, 'hreflang="es" href="https://franciscowebdev.com/es/projects/alpha"'));
libxml_use_internal_errors(true); $check('sitemap is valid XML', simplexml_load_string($sm) !== false);

echo "== static fallback (no projects in the database)\n";
Project::query()->delete();
$check('sample projects get pages too', $get('/projects/mae')->getStatusCode() === 200 && $get('/projects/gamehaven')->getStatusCode() === 200 && $get('/projects/blog-page')->getStatusCode() === 200);

echo "== scripts\n";
$js = file_get_contents('resources/js/site.js');
$check('old #projects/<slug> links are forwarded', str_contains($js, 'redirectOldProjectLinks') && str_contains($js, 'initProjectPage') && str_contains($js, "from './gallery'"));
$check('AI prompts point at /projects/<slug>', str_contains(file_get_contents('app/Services/Ai/AiWriter.php'), '/projects/some-project') && ! str_contains(file_get_contents('resources/js/seo-check.js'), '#projects/'));

foreach (glob("$dir/*") as $f) @unlink($f); @rmdir($dir);
echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
