<?php
// Plan 7: anonymous visit counts and the admin Visits page.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\{Cache, DB, Schema}; use App\Models\{Post, Project}; use App\Services\Stats;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };

$BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';
$visit = function (string $method, string $path, array $headers = [], array $data = [], bool $admin = false) use ($kernel, $app, $BROWSER) {
    app('cookie')->flushQueuedCookies();
    $app['session']->driver()->flush();
    $server = ['HTTP_USER_AGENT' => $BROWSER];
    foreach ($headers as $k => $v) { $server['HTTP_' . strtoupper(str_replace('-', '_', $k))] = $v; }
    if (array_key_exists('User-Agent', $headers)) { $server['HTTP_USER_AGENT'] = $headers['User-Agent']; if ($headers['User-Agent'] === null) $server['HTTP_USER_AGENT'] = ''; }
    $req = Request::create('https://franciscowebdev.com' . $path, $method, $data, [], [], $server);
    if ($admin) { $req->setLaravelSession($app['session']->driver()); $req->session()->start(); $req->session()->put('is_admin', true); $req->session()->put('admin_last_seen', time()); $req->request->set('_token', $req->session()->token()); }
    $res = $kernel->handle($req);
    $kernel->terminate($req, $res);   // counting happens here, after the page is sent

    return $res;
};
$count = fn (string $kind, ?string $name = null) => (int) DB::table('stats')->where('kind', $kind)->when($name !== null, fn ($q) => $q->where('name', $name))->sum('total');
$total = fn () => (int) DB::table('stats')->sum('total');

Project::create(['title' => 'Alpha', 'slug' => 'alpha', 'description' => 'D']);
Post::create(['title' => 'Hello', 'slug' => 'hello', 'body' => 'b', 'status' => 'published', 'published_at' => now()]);

echo "== what is counted\n";
$r = $visit('GET', '/');
$check('a visit to the home page counts once, as "direct"', $count('page', '/') === 1 && $count('ref', 'direct') === 1 && $r->getStatusCode() === 200);
$visit('GET', '/', ['Referer' => 'https://www.google.com/']);
$check('an outside referrer is counted by site name (www removed)', $count('ref', 'google.com') === 1);
$visit('GET', '/pt', ['Referer' => 'https://franciscowebdev.com/']);
$check('moving around the site is not a "referrer"', $count('ref', 'franciscowebdev.com') === 0 && $count('ref') === 2 && $count('page', '/pt') === 1);
$visit('GET', '/blog?tag=laravel&q=secret');
$check('query strings are never stored', $count('page', '/blog') === 1 && DB::table('stats')->where('name', 'like', '%secret%')->count() === 0 && DB::table('stats')->where('name', 'like', '%?%')->count() === 0);
$visit('GET', '/blog/hello'); $visit('GET', '/projects/alpha'); $visit('GET', '/es/projects/alpha'); $visit('GET', '/pt/blog'); $visit('GET', '/privacy');
$check('posts, projects, other languages and the privacy page are counted by their address', $count('page', '/blog/hello') === 1 && $count('page', '/projects/alpha') === 1 && $count('page', '/es/projects/alpha') === 1 && $count('page', '/pt/blog') === 1 && $count('page', '/privacy') === 1);
$visit('GET', '/');
$check('the same page again adds one (single row per day)', $count('page', '/') === 3 && DB::table('stats')->where('kind', 'page')->where('name', '/')->count() === 1);

echo "== what is NOT counted\n";
$before = $total();
$visit('GET', '/', ['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)']);
$visit('GET', '/', ['User-Agent' => 'Mozilla/5.0 Chrome/126 Lighthouse']);
$visit('GET', '/', ['User-Agent' => 'UptimeRobot/2.0']);
$visit('GET', '/', ['User-Agent' => 'curl/8.4.0']);
$visit('GET', '/', ['User-Agent' => 'site-check']);
$visit('GET', '/', ['User-Agent' => '']);
$visit('GET', '/', ['User-Agent' => null]);
$check('robots, monitors, tools and requests with no browser name', $total() === $before);
$visit('GET', '/', ['DNT' => '1']); $visit('GET', '/', ['Sec-GPC' => '1']);
$check('"Do Not Track" and Global Privacy Control are respected', $total() === $before);
$visit('GET', '/', [], [], true); $visit('GET', '/blog/hello', [], [], true);
$check('the admin browsing the site is not a visit', $total() === $before);
foreach (['/sitemap.xml', '/blog/feed.xml', '/health', '/up', '/og/en/blog/hello.png', '/nope-404', '/blog/missing', '/projects/missing', '/lang/pt', '/admin/login', '/about'] as $path) { $visit('GET', $path); }
$check('feeds, sitemap, images, health, 404s, redirects and admin pages are not counted', $total() === $before);
$visit('POST', '/contact', [], ['name' => 'x']);
$check('form posts are not page views', $total() === $before);

echo "== button clicks\n";
$r = $visit('POST', '/e', [], ['e' => 'hero_contact']);
$check('a click is counted and the answer is empty (204), without a cookie', $r->getStatusCode() === 204 && $count('event', 'hero_contact') === 1 && $r->headers->get('Set-Cookie') === null && $app['cookie']->getQueuedCookies() === []);
$visit('POST', '/e', [], ['e' => 'service_ask']); $visit('POST', '/e', [], ['e' => 'service_ask']);
$check('clicks add up', $count('event', 'service_ask') === 2);
$n = $total();
$visit('POST', '/e', [], ['e' => 'made_up_name']); $visit('POST', '/e', [], ['e' => str_repeat('a', 400)]); $visit('POST', '/e', [], []); $visit('POST', '/e', [], ['e' => ['x']]);
$check('names that are not on the list are ignored (no junk rows)', $total() === $n);
$visit('POST', '/e', [], ['e' => 'lead']);
$check('the "brief sent" number cannot be inflated from the browser', $count('event', 'lead') === 0);
$visit('POST', '/e', ['User-Agent' => 'Googlebot'], ['e' => 'hero_contact']); $visit('POST', '/e', ['DNT' => '1'], ['e' => 'hero_contact']); $visit('POST', '/e', [], ['e' => 'hero_contact'], true);
$check('clicks from robots, Do-Not-Track visitors and the admin are ignored', $count('event', 'hero_contact') === 1);
$view = $visit('GET', '/')->getContent();
$check('the page marks its buttons for counting', substr_count($view, 'data-track="hero_projects"') === 1 && str_contains($view, 'data-track="hero_contact"') && str_contains($view, 'data-track="service_ask"') && str_contains($view, 'data-track="project_open"'));
$check('project page buttons are marked', str_contains($visit('GET', '/projects/alpha')->getContent(), 'data-track="project_cta"'));
$js = file_get_contents('resources/js/track.js');
$check('the browser script sends a tiny beacon and honours Do Not Track', str_contains($js, 'sendBeacon') && str_contains($js, 'doNotTrack') && str_contains($js, 'globalPrivacyControl') && str_contains(file_get_contents('resources/js/site.js'), 'initTracking();'));

echo "== a brief sent through the form counts as a lead\n";
Cache::flush();
$ref = new ReflectionMethod(App\Support\FormToken::class, 'sign'); $t = time() - 10;
$visit('POST', '/contact', [], ['_ft' => $t . '.' . $ref->invoke(null, $t), 'name' => 'Ana', 'email' => 'ana@example.com', 'project_type' => 'business', 'description' => 'Hello there']);
$check('lead event recorded by the server', $count('event', 'lead') === 1);

echo "== the report\n";
$rep = Stats::report(7);
$check('totals and daily series (7 entries, today last)', $rep['views'] === $count('page') && $rep['series']->count() === 7 && $rep['series']->last()['day'] === now()->toDateString() && $rep['series']->last()['views'] === $rep['views']);
$check('top pages, projects, posts and referrers', $rep['pages']->keys()->first() === '/' && $rep['projects']->keys()->sort()->values()->all() === ['/es/projects/alpha', '/projects/alpha'] && $rep['posts']->keys()->all() === ['/blog/hello'] && $rep['referrers']->keys()->sort()->values()->all() === ['direct', 'google.com']);
$check('language split follows the address', $rep['languages']['pt'] === 2 && $rep['languages']['es'] === 1 && $rep['languages']['en'] === $rep['views'] - 3, json_encode($rep['languages']));
$check('events and leads', $rep['leads'] === 1 && $rep['events']['hero_contact'] === 1);
DB::table('stats')->insert(['day' => now()->subDays(40)->toDateString(), 'kind' => 'page', 'name' => '/old', 'total' => 99]);
$check('older data stays out of a shorter range', Stats::report(30)['views'] === $rep['views'] && Stats::report(90)['views'] === $rep['views'] + 99);

echo "== admin page\n";
$h = $visit('GET', '/admin/stats', [], [], true)->getContent();
$check('shows the numbers, chart and top lists', str_contains($h, 'Page views') && str_contains($h, 'Views per day') && str_contains($h, '/blog/hello') && str_contains($h, 'google.com') && str_contains($h, 'Briefs sent') && str_contains($h, 'Hero: Start a project'));
$check('7, 30 and 90 day ranges; anything else falls back to 30', str_contains($visit('GET', '/admin/stats?days=7', [], [], true)->getContent(), 'Busiest day') && str_contains($visit('GET', '/admin/stats?days=999', [], [], true)->getContent(), 'last 30 days'));
$check('needs the admin login', str_contains((string) $visit('GET', '/admin/stats')->headers->get('Location'), 'login'));
$check('nav has a Visits link', str_contains($h, '>Visits</a>'));

echo "== before the migration / when the table is unavailable\n";
Schema::drop('stats');
$r = $visit('GET', '/'); $r2 = $visit('POST', '/e', [], ['e' => 'hero_contact']); $page = $visit('GET', '/admin/stats', [], [], true);
$check('visitors see no error and the admin sees a friendly note', $r->getStatusCode() === 200 && $r2->getStatusCode() === 204 && $page->getStatusCode() === 200 && str_contains($page->getContent(), 'has not been created yet'));

echo "== privacy notice\n";
foreach (['/privacy' => 'anonymous daily totals', '/pt/privacy' => 'totais diários anónimos', '/es/privacy' => 'totales diarios anónimos'] as $u => $needle) {
    $check("$u explains the visit counts", str_contains($visit('GET', $u)->getContent(), $needle));
}
$check('it no longer claims "no analytics"', ! str_contains($visit('GET', '/privacy')->getContent(), 'no analytics and no advertising'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
