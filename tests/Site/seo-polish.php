<?php
// Home titles and descriptions, service-provider data, breadcrumbs on posts, honest sitemap dates, large-image previews.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\DB; use App\Models\{Post, Project};
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path));
$meta = fn (string $html, string $re) => preg_match($re, $html, $m) ? html_entity_decode($m[1]) : '';

$post = Post::create(['title' => 'Hello', 'slug' => 'hello', 'body' => 'b', 'excerpt' => 'Short summary', 'status' => 'published', 'published_at' => '2026-03-01 10:00:00', 'translations' => ['pt' => ['title' => 'Olá']]]);
DB::table('posts')->where('id', $post->id)->update(['updated_at' => '2026-03-02 10:00:00']);
$project = Project::create(['title' => 'Alpha', 'slug' => 'alpha', 'description' => 'D']);
DB::table('projects')->where('id', $project->id)->update(['updated_at' => '2026-02-10 10:00:00']);

echo "== home titles and descriptions\n";
$expect = ['/' => ['en', 'Web Developer near Almada and Seixal', 'Seixal'], '/pt' => ['pt', 'Criação de Sites em Almada e Seixal', 'Seixal'], '/es' => ['es', 'Desarrollador Web cerca de Almada y Seixal', 'Seixal']];
foreach ($expect as $path => [$loc, $titleStart, $needle]) {
    $h = $get($path)->getContent();
    $title = $meta($h, '/<title>([^<]*)<\/title>/'); $desc = $meta($h, '/<meta name="description" content="([^"]*)"/');
    $check("$loc: title starts with what people search, fits a results page (<= 62)", str_starts_with($title, $titleStart) && mb_strlen($title) <= 62, "$title (" . mb_strlen($title) . ')');
    $check("$loc: description mentions the place and fits (110-160 characters)", str_contains($desc, $needle) && mb_strlen($desc) >= 110 && mb_strlen($desc) <= 160, $desc . ' (' . mb_strlen($desc) . ')');
    $check("$loc: the same text is used for shared links (og and twitter)", str_contains($h, 'property="og:title" content="' . htmlspecialchars($title, ENT_QUOTES) . '"') && str_contains($h, 'name="twitter:description"'));
    $check("$loc: no more 'student' pitch in the description", ! preg_match('/student|estudante|estudiante/i', $desc));
}

echo "== structured data on the home page\n";
preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $get('/pt')->getContent(), $m); $ld = collect($m[1])->map(fn ($j) => json_decode($j, true));
$svc = $ld->firstWhere('@type', 'ProfessionalService');
$check('Person, ProfessionalService and FAQPage are all present and valid JSON', $ld->count() === 3 && $ld->pluck('@type')->sort()->values()->all() === ['FAQPage', 'Person', 'ProfessionalService'], $ld->pluck('@type')->implode(','));
$check('service listing: place, languages, services, links', $svc && $svc['address']['addressLocality'] === 'Seixal' && $svc['address']['addressCountry'] === 'PT' && array_intersect(['Almada', 'Seixal', 'Portugal', 'Worldwide'], $svc['areaServed']) === ['Almada', 'Seixal', 'Portugal', 'Worldwide'] && $svc['knowsLanguage'] === ['pt', 'en', 'es'] && count($svc['serviceType']) === 4 && str_contains($svc['serviceType'][0], 'Sites') && count($svc['sameAs']) === 2 && str_contains($svc['url'], '/pt'), json_encode($svc));

echo "== breadcrumbs on posts\n";
foreach (['/blog/hello' => ['en', 'Blog', 'Hello', 'https://franciscowebdev.com/blog/hello'], '/pt/blog/hello' => ['pt', 'Blog', 'Olá', 'https://franciscowebdev.com/pt/blog/hello']] as $path => [$loc, $blogName, $title, $url]) {
    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $get($path)->getContent(), $m); $ld = collect($m[1])->map(fn ($j) => json_decode($j, true));
    $bc = $ld->firstWhere('@type', 'BreadcrumbList');
    $check("$loc: Home > Blog > post with the right addresses", $bc && count($bc['itemListElement']) === 3 && $bc['itemListElement'][2]['name'] === $title && $bc['itemListElement'][2]['item'] === $url && str_contains($bc['itemListElement'][1]['item'], 'blog') && $ld->firstWhere('@type', 'BlogPosting') !== null, json_encode($bc));
}

echo "== large-image previews\n";
foreach (['/', '/pt', '/blog', '/blog/hello', '/projects/alpha', '/privacy'] as $path) {
    $check("$path allows large pictures in results", str_contains($get($path)->getContent(), '<meta name="robots" content="max-image-preview:large">'));
}
$draft = Post::create(['title' => 'Secret', 'slug' => 'secret', 'body' => 'b', 'status' => 'draft']);
$req = Request::create('https://franciscowebdev.com/blog/secret'); $req->setLaravelSession($app['session']->driver()); $req->session()->start(); $req->session()->put('is_admin', true); $req->session()->put('admin_last_seen', time());
$h = $kernel->handle($req)->getContent();
$check('a draft preview stays noindex (and does not also say large pictures)', str_contains($h, 'noindex, nofollow') && ! str_contains($h, 'max-image-preview'));
$draft->delete();

echo "== sitemap dates\n";
config(['portfolio.site_updated' => '2026-01-15', 'portfolio.privacy_updated' => '2026-01-20']);
$xml = $get('/sitemap.xml')->getContent();
$dateOf = function (string $loc) use (&$xml) { return preg_match('/<loc>' . preg_quote($loc, '/') . '<\/loc>\s*<lastmod>([^<]+)<\/lastmod>/', $xml, $m) ? $m[1] : null; };
$check('valid XML', @simplexml_load_string($xml) !== false);
$check('home date = newest change to posts or projects (not today)', $dateOf('https://franciscowebdev.com') === '2026-03-02' && $dateOf('https://franciscowebdev.com/pt') === '2026-03-02', (string) $dateOf('https://franciscowebdev.com'));
$check('blog index date = newest post change', $dateOf('https://franciscowebdev.com/blog') === '2026-03-02' && $dateOf('https://franciscowebdev.com/es/blog') === '2026-03-02');
$check('privacy date comes from the setting', $dateOf('https://franciscowebdev.com/privacy') === '2026-01-20' && $dateOf('https://franciscowebdev.com/pt/privacy') === '2026-01-20');
$check('project and post pages keep their own dates', $dateOf('https://franciscowebdev.com/projects/alpha') === '2026-02-10' && $dateOf('https://franciscowebdev.com/blog/hello') === '2026-03-02');
$check('nothing in the sitemap says "today" just because it was requested', ! str_contains($xml, now()->toDateString()));
DB::table('posts')->where('id', $post->id)->update(['updated_at' => '2026-06-06 08:00:00']);
$xml = $get('/sitemap.xml')->getContent();
$check('editing a post moves the blog and home dates', $dateOf('https://franciscowebdev.com/blog') === '2026-06-06' && $dateOf('https://franciscowebdev.com') === '2026-06-06' && $dateOf('https://franciscowebdev.com/privacy') === '2026-01-20');
config(['portfolio.site_updated' => '2027-01-01']);
$xml = $get('/sitemap.xml')->getContent();
$check('raising site_updated (after a redesign) moves the home page date', $dateOf('https://franciscowebdev.com') === '2027-01-01');
Project::query()->delete(); Post::query()->delete();
$xml = $get('/sitemap.xml')->getContent();
$check('works with no posts at all (sample projects)', @simplexml_load_string($xml) !== false && $dateOf('https://franciscowebdev.com') === '2027-01-01' && str_contains($xml, '/projects/mae'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
