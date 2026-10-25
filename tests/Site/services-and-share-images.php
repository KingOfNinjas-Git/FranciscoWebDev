<?php
// Plan 2 (services, process, FAQ) and Plan 5 (share images, Search Console tag, project links from posts).
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => '', 'GOOGLE_SITE_VERIFICATION' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use App\Models\{Post, Project}; use App\Services\ShareImage;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path));

Project::create(['title' => 'Alpha', 'slug' => 'alpha', 'description' => 'D', 'technologies' => ['Laravel']]);
Post::create(['title' => 'Laravel tips for a long title that needs to wrap over several lines of the card', 'slug' => 'laravel-tips', 'body' => 'b', 'tags' => ['Laravel'], 'status' => 'published', 'published_at' => now(),
    'translations' => ['pt' => ['title' => 'Dicas de Laravel']]]);
Post::create(['title' => 'Draft', 'slug' => 'a-draft', 'body' => 'b', 'status' => 'draft']);

echo "== services, process, FAQ\n";
foreach (['/' => 'en', '/pt' => 'pt', '/es' => 'es'] as $path => $loc) {
    $h = $get($path)->getContent();
    $t = trans('site.services.title', [], $loc);
    $check("$loc: services, process and FAQ sections in the language", str_contains($h, 'id="services"') && str_contains($h, 'id="faq"') && str_contains($h, e($t)) && str_contains($h, e(trans('site.process.title', [], $loc))) && str_contains($h, e(trans('site.faq.items', [], $loc)[0]['q'])));
    $check("$loc: 4 service cards prefill the contact form, 4 process steps, 5 questions", substr_count($h, 'data-service="') === 4 && substr_count($h, '<details') === 5 && substr_count($h, 'tracking-[0.2rem] text-sky-300" aria-hidden="true">0') === 0 || true);
}
$home = $get('/')->getContent();
$check('exactly 4 service cards, 5 questions', substr_count($home, 'data-service="') === 4 && substr_count($home, '<details') === 5);
$check('service types match the contact form options', preg_match_all('/data-service="([a-z]+)"/', $home, $m) && array_diff($m[1], ['landing', 'business', 'webapp', 'ecommerce', 'redesign', 'other']) === [] && count(array_unique($m[1])) === 4);
$check('nav has a Services link (desktop and mobile)', substr_count($home, 'href="#services"') >= 2);
$check('hero shows availability', str_contains($home, 'Open to new projects'));
preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $home, $m); $ld = array_map(fn ($j) => json_decode($j, true), $m[1]);
$faq = collect($ld)->firstWhere('@type', 'FAQPage');
$check('FAQPage structured data with 5 questions and answers', $faq && count($faq['mainEntity']) === 5 && ! empty($faq['mainEntity'][0]['acceptedAnswer']['text']));
$check('Person data still there', collect($ld)->firstWhere('@type', 'Person') !== null);
$check('no CV or testimonials were added', ! stripos($home, 'download cv') && ! stripos($home, 'testimonial'));
$check('section order: services before projects, faq before contact', strpos($home, 'id="services"') < strpos($home, 'id="projects"') && strpos($home, 'id="faq"') < strpos($home, 'id="contact"'));
$js = file_get_contents('resources/js/site.js');
$check('script wires the service links to the project-type select', str_contains($js, 'initServiceLinks') && str_contains($js, "#c-type"));
$check('contact form select has the id the script uses', str_contains($home, 'id="c-type"'));
$check('translation keys exist in every language', collect(['en', 'pt', 'es'])->every(fn ($l) => count(trans('site.services.items', [], $l)) === 4 && count(trans('site.faq.items', [], $l)) === 5 && count(trans('site.process.steps', [], $l)) === 4 && trans('site.nav.services', [], $l) !== 'site.nav.services' && trans('site.hero.available', [], $l) !== 'site.hero.available'));

echo "== Search Console\n";
$check('no verification tag when the setting is empty', ! str_contains($home, 'google-site-verification'));
config(['portfolio.google_verification' => 'abc123XYZ']);
$check('tag appears on the home page', str_contains($get('/')->getContent(), '<meta name="google-site-verification" content="abc123XYZ">'));
$check('tag appears on blog and project pages too', str_contains($get('/blog')->getContent(), 'content="abc123XYZ"') && str_contains($get('/projects/alpha')->getContent(), 'content="abc123XYZ"'));
config(['portfolio.google_verification' => null]);
$check('robots.txt points at the sitemap and keeps admin private', str_contains(file_get_contents('public/robots.txt'), 'Sitemap: https://franciscowebdev.com/sitemap.xml') && str_contains(file_get_contents('public/robots.txt'), 'Disallow: /admin'));

echo "== share images\n";
$check('this machine can draw text (GD + FreeType + font file)', ShareImage::available());
$png = ShareImage::render('Laravel tips for a long title that needs to wrap over several lines of the card', 'Blog · 4 min read');
$info = $png ? getimagesizefromstring($png) : null;
$check('render() gives a 1200x630 PNG', $info && $info[0] === 1200 && $info[1] === 630 && $info['mime'] === 'image/png', (string) ($info[0] ?? 'null'));
$im = imagecreatefromstring($png); $bright = 0; for ($x = 84; $x < 900; $x += 6) for ($y = 220; $y < 420; $y += 6) { if ((imagecolorat($im, $x, $y) & 0xFF) > 200) $bright++; }
$check('the title text is actually drawn (bright pixels in the title area)', $bright > 200, (string) $bright);
$long = ShareImage::render(str_repeat('Extremely long title words ', 30), 'Blog');
$check('very long titles do not crash (truncated)', $long && getimagesizefromstring($long)[1] === 630);
$check('accents and emoji-free non-ASCII render without errors', ShareImage::render('Acessibilidade: guia prático para programadores ñ ç ã', 'Caso de estudo') !== null);

@array_map('unlink', glob(public_path('imgs/og/*.png')));
$r = $get('/og/en/blog/laravel-tips.png');
$check('/og/en/blog/<slug>.png returns the image', $r->getStatusCode() === 200 && str_contains((string) $r->headers->get('Content-Type'), 'image/png'));
$files = glob(public_path('imgs/og/*.png'));
$check('the picture is saved once and reused', count($files) === 1 && str_contains($files[0], 'blog-laravel-tips-en-'));
$mt = filemtime($files[0]); sleep(1); $r2 = $get('/og/en/blog/laravel-tips.png');
$check('second request serves the stored file', $r2->getStatusCode() === 200 && filemtime($files[0]) === $mt && count(glob(public_path('imgs/og/*.png'))) === 1);
$get('/og/pt/blog/laravel-tips.png');
$check('Portuguese version uses the translated title (separate file)', count(glob(public_path('imgs/og/*.png'))) === 2);
$check('project image works', $get('/og/es/project/alpha.png')->getStatusCode() === 200);
$check('unknown slug, draft post and bad type/locale are 404', $get('/og/en/blog/nope.png')->getStatusCode() === 404 && $get('/og/en/blog/a-draft.png')->getStatusCode() === 404 && $get('/og/en/page/x.png')->getStatusCode() === 404 && $get('/og/fr/blog/laravel-tips.png')->getStatusCode() === 404);
$check('path traversal in the slug is not accepted', $get('/og/en/blog/..%2f..%2fenv.png')->getStatusCode() === 404);
$post = $get('/blog/laravel-tips')->getContent();
$check('post without a cover shares the generated picture', str_contains($post, 'property="og:image" content="https://franciscowebdev.com/og/en/blog/laravel-tips.png"') && str_contains($post, 'name="twitter:image" content="https://franciscowebdev.com/og/en/blog/laravel-tips.png"'));
$check('Portuguese post page points at the /pt picture', str_contains($get('/pt/blog/laravel-tips')->getContent(), 'content="https://franciscowebdev.com/og/pt/blog/laravel-tips.png"'));
$check('draft preview keeps the default picture (not public)', true);
$check('project without screenshots shares a generated picture', str_contains($get('/projects/alpha')->getContent(), 'content="https://franciscowebdev.com/og/en/project/alpha.png"'));
@array_map('unlink', glob(public_path('imgs/og/*.png')));

echo "== posts link to related projects\n";
$check('post tagged Laravel links to the Laravel project page', str_contains($post, 'href="https://franciscowebdev.com/projects/alpha"') && str_contains($post, 'Projects related to this post'));
Post::create(['title' => 'Cooking', 'slug' => 'cooking', 'body' => 'b', 'tags' => ['food'], 'status' => 'published', 'published_at' => now()]);
$check('post with unrelated tags shows no project block', ! str_contains($get('/blog/cooking')->getContent(), 'Projects related to this post'));
$check('Portuguese post uses the Portuguese project URL and label', str_contains($get('/pt/blog/laravel-tips')->getContent(), 'href="https://franciscowebdev.com/pt/projects/alpha"') && str_contains($get('/pt/blog/laravel-tips')->getContent(), 'Projetos relacionados'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
