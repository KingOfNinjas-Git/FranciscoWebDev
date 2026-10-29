<?php
// Client testimonials: hidden until published, in three languages, managed from the admin.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);


use Illuminate\Http\Request; use App\Models\Testimonial; use App\Http\Controllers\TestimonialAdminController;
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path));
$ctl = $app->make(TestimonialAdminController::class);
$mk = function ($method, $data = []) use ($app) { $r = Request::create('/x', $method, $data); $r->setLaravelSession($app['session']->driver()); $app->instance('request', $r); return $r; };
$admin = function (string $path) use ($app, $kernel) { $r = Request::create('https://franciscowebdev.com' . $path); $r->setLaravelSession($app['session']->driver()); $r->session()->put('is_admin', true); $r->session()->put('admin_last_seen', time()); return $kernel->handle($r); };

echo "== nothing to show\n";
foreach (['/', '/pt', '/es'] as $path) {
    $h = $get($path)->getContent();
    $check("$path: no quotes, no section, no heading", ! str_contains($h, 'id="testimonials"') && ! str_contains($h, 'What clients say') && ! str_contains($h, 'O que dizem os clientes'));
}

echo "== saving quotes\n";
$ctl->store($mk('POST', ['name' => 'Ana Silva', 'role' => 'Owner, Padaria Central', 'quote' => 'Francisco built us a site that finally looks like our bakery.', 'translations' => ['pt' => ['quote' => 'O Francisco fez-nos um site que finalmente parece a nossa padaria.']], 'source_url' => 'https://www.linkedin.com/in/ana', 'position' => 2]));
$ctl->store($mk('POST', ['name' => 'Rui Costa', 'quote' => 'Clear plan, fair price, done on time.', 'published' => '1', 'position' => 1]));
$ctl->store($mk('POST', ['name' => 'Hidden Person', 'quote' => 'Not published yet.']));
$ana = Testimonial::firstWhere('name', 'Ana Silva');
$check('a new quote is hidden unless "published" is ticked', ! $ana->published && Testimonial::firstWhere('name', 'Rui Costa')->published && ! Testimonial::firstWhere('name', 'Hidden Person')->published);
$check('the Portuguese quote and the link are kept', $ana->translations['pt']['quote'] === 'O Francisco fez-nos um site que finalmente parece a nossa padaria.' && $ana->source_url === 'https://www.linkedin.com/in/ana' && ! isset($ana->translations['es']));
foreach ([['name' => '', 'quote' => 'x'], ['name' => 'X', 'quote' => ''], ['name' => 'X', 'quote' => str_repeat('a', 1201)], ['name' => 'X', 'quote' => 'ok', 'source_url' => 'javascript:alert(1)']] as $bad) {
    try { $ctl->store($mk('POST', $bad)); $refused = false; } catch (Illuminate\Validation\ValidationException $e) { $refused = true; }
    if (! $refused) { $check('refused: ' . json_encode($bad), false); }
}
$check('bad quotes (no name, no text, too long, a script link) are refused', Testimonial::count() === 3);

echo "== the home page\n";
$h = $get('/')->getContent();
$check('only the published quote is shown, with its section', str_contains($h, 'id="testimonials"') && str_contains($h, 'What clients say') && str_contains($h, 'Clear plan, fair price, done on time.') && str_contains($h, 'Rui Costa') && ! str_contains($h, 'Ana Silva') && ! str_contains($h, 'Not published yet.'));
$ctl->update($mk('PUT', ['name' => 'Ana Silva', 'role' => 'Owner, Padaria Central', 'quote' => 'Francisco built us a site that finally looks like our bakery.', 'translations' => ['pt' => ['quote' => 'O Francisco fez-nos um site que finalmente parece a nossa padaria.']], 'source_url' => 'https://www.linkedin.com/in/ana', 'position' => 2, 'published' => '1']), $ana->id);
$h = $get('/')->getContent();
$check('publishing shows it, in the chosen order (position 1 before 2)', str_contains($h, 'Ana Silva') && strpos($h, 'Rui Costa') < strpos($h, 'Ana Silva') && str_contains($h, 'Owner, Padaria Central'));
$check('the link to where it was shared opens safely', str_contains($h, 'href="https://www.linkedin.com/in/ana" target="_blank" rel="noopener noreferrer"') && str_contains($h, 'See where this was shared'));
$pt = $get('/pt')->getContent(); $es = $get('/es')->getContent();
$check('Portuguese: translated heading and translated quote', str_contains($pt, 'O que dizem os clientes') && str_contains($pt, 'O Francisco fez-nos um site que finalmente parece a nossa padaria.'));
$check('Spanish: translated heading, original quote as the fallback', str_contains($es, 'Lo que dicen los clientes') && str_contains($es, 'Francisco built us a site that finally looks like our bakery.'));
$check('quotes are escaped, never raw HTML', (function () use ($ctl, $mk, $get) { $ctl->store($mk('POST', ['name' => 'Eve', 'quote' => '<script>alert(1)</script> hi', 'published' => '1'])); $html = $get('/')->getContent(); return ! str_contains($html, '<script>alert(1)') && str_contains($html, '&lt;script&gt;alert(1)'); })());

echo "== the admin\n";
$idx = $admin('/admin/testimonials');
$check('the list page opens and shows both states and the asking messages', $idx->getStatusCode() === 200 && str_contains($idx->getContent(), 'Published') && str_contains($idx->getContent(), 'Hidden') && str_contains($idx->getContent(), 'Asking for a quote') && str_contains($idx->getContent(), 'Olá [nome]'));
$check('the add and edit pages open', $admin('/admin/testimonials/create')->getStatusCode() === 200 && $admin('/admin/testimonials/' . $ana->id . '/edit')->getStatusCode() === 200);
$app['session']->driver()->flush(); // the test's own admin session is over
$check('a visitor cannot open the admin pages',$get('/admin/testimonials')->isRedirect() && str_contains($get('/admin/testimonials')->headers->get('Location'), 'login'));
$ctl->destroy($ana->id);
$check('a quote can be deleted', Testimonial::find($ana->id) === null && ! str_contains($get('/')->getContent(), 'Ana Silva'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
