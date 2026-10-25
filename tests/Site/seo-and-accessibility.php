<?php
// Checks for: security headers + CSP, www redirect, error pages, accessibility, fonts, existing-image optimizer.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use App\Models\{Project, ProjectImage, Post};
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> $extra" : '') . "\n"; $ok ? $pass++ : $fail++; };
$get = function ($url, $headers = []) use ($kernel) { $r = Request::create($url, 'GET', [], [], [], $headers); return $kernel->handle($r); };

echo "== 1. security headers + CSP\n";
$r = $get('https://franciscowebdev.com/'); $h = $r->headers; $html = $r->getContent();
$check('home 200', $r->getStatusCode() === 200, (string) $r->getStatusCode());
$check('X-Content-Type-Options nosniff', $h->get('X-Content-Type-Options') === 'nosniff');
$check('X-Frame-Options SAMEORIGIN', $h->get('X-Frame-Options') === 'SAMEORIGIN');
$check('Referrer-Policy set', $h->get('Referrer-Policy') === 'strict-origin-when-cross-origin');
$check('Permissions-Policy set', str_contains((string) $h->get('Permissions-Policy'), 'camera=()'));
$check('HSTS set (production + https URL)', str_contains((string) $h->get('Strict-Transport-Security'), 'max-age=31536000'));
$csp = (string) $h->get('Content-Security-Policy');
preg_match("/'nonce-([^']+)'/", $csp, $m); $nonce = $m[1] ?? '';
$check('CSP present with script nonce', $nonce !== '' && str_contains($csp, "default-src 'self'") && str_contains($csp, "object-src 'none'") && str_contains($csp, "frame-ancestors 'self'"));
$check('CSP does not allow unsafe-inline scripts', !preg_match("/script-src[^;]*unsafe-inline/", $csp) && !preg_match("/script-src[^;]*unsafe-eval/", $csp));
// every executable <script> must be external (self) or carry the matching nonce
preg_match_all('/<script\b([^>]*)>/i', $html, $tags, PREG_SET_ORDER);
$bad = [];
foreach ($tags as $t) {
    $a = $t[1];
    if (preg_match('/type="application\/(ld\+)?json"/', $a)) continue;           // data block, never executed
    if (preg_match('/\bsrc="/', $a) && str_contains($a, 'nonce="' . $nonce . '"')) continue; // Vite module script
    if (preg_match('/\bsrc="\/build\//', $a)) continue;                             // same-origin file
    if (str_contains($a, 'nonce="' . $nonce . '"')) continue;                       // inline with nonce
    $bad[] = $a;
}
$check('every executable script is same-origin or has the nonce', $bad === [] && count($tags) >= 3, json_encode($bad));
$check('inline js-class script uses the same nonce', str_contains($html, 'nonce="' . $nonce . '">document.documentElement.classList.add'));
$check('no inline event handlers / javascript: urls in page', !preg_match('/\son(click|submit|load|error)=|href="javascript:/i', $html));
$check('no external hosts loaded by the page', !preg_match('#(src|href)="https?://(?!franciscowebdev\.com|www\.linkedin\.com|github\.com)[^"]*\.(js|css|woff2?)#i', $html));
$check('nonce changes every request', (string) $get('https://franciscowebdev.com/')->headers->get('Content-Security-Policy') !== $csp);
config(['security.csp' => false]);
$check('SECURITY_CSP=false switches only the CSP off', $get('https://franciscowebdev.com/')->headers->get('Content-Security-Policy') === null && $get('https://franciscowebdev.com/')->headers->get('X-Frame-Options') === 'SAMEORIGIN');
config(['security.csp' => true]);
foreach (['/blog', '/pt', '/es/blog', '/admin/login'] as $u) {
    $c = (string) $get('https://franciscowebdev.com' . $u)->headers->get('Content-Security-Policy');
    $check("CSP on $u", str_contains($c, "'nonce-"));
}

echo "== 2. www redirect\n";
$r = $get('https://www.franciscowebdev.com/blog?tag=laravel');
$check('www -> apex 301, path + query kept', $r->getStatusCode() === 301 && $r->headers->get('Location') === 'https://franciscowebdev.com/blog?tag=laravel', (string) $r->headers->get('Location'));
$check('apex not redirected', $get('https://franciscowebdev.com/blog')->getStatusCode() === 200);
$check('other www host untouched (no open redirect)', $get('https://www.evil.com/blog')->getStatusCode() !== 301);
$check('lookalike host untouched', $get('https://www.franciscowebdev.com.evil.com/')->getStatusCode() !== 301);
config(['security.redirect_www' => false]);
$check('REDIRECT_WWW=false disables it', $get('https://www.franciscowebdev.com/')->getStatusCode() === 200);
config(['security.redirect_www' => true]);

echo "== 3. themed error pages\n";
foreach (['en', 'pt', 'es'] as $loc) {
    app()->setLocale($loc);
    foreach ([403, 404, 419, 429, 500, 503] as $code) {
        $out = view("errors.$code")->render();
        $title = __("site.errors.$code.title");
        $ok = str_contains($out, $title) && str_contains($out, (string) $code) && str_contains($out, 'noindex') && !str_contains($out, 'Illuminate');
        if (!$ok) $check("errors.$code ($loc)", false, 'missing title/code');
    }
}
$check('403/404/419/429/500/503 render in en/pt/es with translated text', true);
app()->setLocale('en');
$r = $get('https://franciscowebdev.com/this-page-does-not-exist');
$check('unknown URL -> themed 404 page', $r->getStatusCode() === 404 && str_contains($r->getContent(), 'Page not found') && str_contains($r->getContent(), 'Back to home'), (string) $r->getStatusCode());
$check('404 page is noindex and has favicon + assets', str_contains($r->getContent(), 'noindex') && str_contains($r->getContent(), 'favicon.svg') && str_contains($r->getContent(), '/build/assets/'));
$r = $get('https://franciscowebdev.com/blog/missing-post'); $check('missing blog post -> themed 404', $r->getStatusCode() === 404 && str_contains($r->getContent(), 'Page not found'));
$check('pt/es error texts differ from English', __('site.errors.404.title', [], 'pt') !== __('site.errors.404.title', [], 'en') && __('site.errors.500.text', [], 'es') !== __('site.errors.500.text', [], 'en'));

echo "== 4. accessibility\n";
$html = $get('https://franciscowebdev.com/')->getContent();
$check('skip link first in body, targets #main', preg_match('/<body[^>]*>\s*<a href="#main" class="skip-link">Skip to main content<\/a>/', $html) === 1);
$check('main has id=main and tabindex=-1', str_contains($html, '<main id="main" tabindex="-1">'));
app()->setLocale('pt'); $ptHtml = $get('https://franciscowebdev.com/pt')->getContent();
$check('skip link translated (pt)', str_contains($ptHtml, 'Saltar para o conte'));
app()->setLocale('en');
$check('blog page has skip link + main target', str_contains($get('https://franciscowebdev.com/blog')->getContent(), 'class="skip-link"') && str_contains($get('https://franciscowebdev.com/blog')->getContent(), '<main id="main"'));
$bag = new Illuminate\Support\ViewErrorBag; $bag->put('default', new Illuminate\Support\MessageBag(['name' => ['The name field is required.'], 'email' => ['Please enter a valid email address.']]));
$form = view('partials.section-contact', ['errors' => $bag])->render();
$check('error summary is focusable alert with links to fields', str_contains($form, 'role="alert" tabindex="-1" data-feedback') && str_contains($form, 'href="#c-name"') && str_contains($form, 'href="#c-email"'));
$check('invalid fields get aria-invalid + aria-describedby', str_contains($form, 'id="c-name"') && preg_match('/id="c-name"[^>]*aria-invalid="true" aria-describedby="err-name"/', $form) === 1 && str_contains($form, 'id="err-name" class="field-error"'));
$check('valid fields are not marked invalid', preg_match('/id="c-description"[^>]*aria-invalid/', $form) === 0);
$check('required fields announce aria-required', substr_count($form, 'aria-required="true"') >= 4);
$clean = view('partials.section-contact', ['errors' => new Illuminate\Support\ViewErrorBag])->render();
$check('clean form has no error markup', !str_contains($clean, 'aria-invalid') && !str_contains($clean, 'data-feedback'));
$low = ''; foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views', FilesystemIterator::SKIP_DOTS)) as $f) { if (preg_match('/text-slate-(500|600)/', file_get_contents($f->getPathname()))) $low .= $f->getPathname() . ' '; }
$check('no low-contrast greys left in views', $low === '', $low);
$css = file_get_contents('resources/css/site.css');
$check('placeholder colour raised, skip-link + field-error styles exist', str_contains($css, 'color: #8493ab') && str_contains($css, '.skip-link') && str_contains($css, '.field-error'));
$check('site.js focuses form feedback after reload', str_contains(file_get_contents('resources/js/site.js'), 'initFormFeedback'));

echo "== 5. self-hosted fonts\n";
$manifest = json_decode(file_get_contents('public/build/manifest.json'), true);
$builtCss = glob('public/build/assets/app-*.css'); $cssText = $builtCss ? file_get_contents($builtCss[0]) : '';
$fonts = glob('public/build/assets/*.woff2');
$check('woff2 files are bundled in the build', count($fonts) >= 4, (string) count($fonts));
$check('@font-face for Inter + Space Grotesk, swap, served from /build', str_contains($cssText, "Inter Variable") && str_contains($cssText, 'Space Grotesk Variable') && str_contains($cssText, 'font-display:swap') && preg_match('#url\(/build/assets/inter-latin-wght-normal-[^)]+\.woff2\)#', $cssText) === 1);
$check('theme font stack uses the loaded fonts', str_contains($cssText, '--font-sans:Inter Variable') || str_contains($cssText, "--font-sans: 'Inter Variable'") || str_contains($cssText, '--font-sans:"Inter Variable"'), substr($cssText, strpos($cssText, '--font-sans') ?: 0, 80));
$check('old unloaded font reference removed', !str_contains(file_get_contents('resources/css/app.css'), 'Instrument Sans'));
$check('no request to any font CDN in the page', !str_contains($html, 'fonts.googleapis') && !str_contains($html, 'fonts.gstatic') && !str_contains($html, 'fonts.bunny'));

echo "== 6. optimise existing images\n";
$dir = public_path('imgs/_optimizer_test'); @mkdir($dir, 0755, true);
$mkimg = function (string $path, string $type) { $im = imagecreatetruecolor(2400, 1600); for ($i = 0; $i < 6000; $i++) { $x = rand(0, 2380); $y = rand(0, 1580); imagefilledrectangle($im, $x, $y, $x + rand(2, 40), $y + rand(2, 40), imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255))); } $type === 'png' ? imagepng($im, $path, 0) : imagejpeg($im, $path, 100); imagedestroy($im); };
$mkimg("$dir/big.png", 'png'); $mkimg("$dir/photo.jpg", 'jpg'); $mkimg("$dir/cover.png", 'png');
file_put_contents("$dir/tiny.png", base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
copy("$dir/tiny.png", "$dir/already.webp");
$p = Project::create(['title' => 'T', 'slug' => 't']);
$rel = 'imgs/_optimizer_test/';
ProjectImage::create(['project_id' => $p->id, 'path' => $rel . 'big.png', 'position' => 0]);
ProjectImage::create(['project_id' => $p->id, 'path' => $rel . 'big.png', 'position' => 1]);   // two rows share one file
ProjectImage::create(['project_id' => $p->id, 'path' => $rel . 'photo.jpg', 'position' => 2]);
ProjectImage::create(['project_id' => $p->id, 'path' => $rel . 'tiny.png', 'position' => 3]);
ProjectImage::create(['project_id' => $p->id, 'path' => $rel . 'already.webp', 'position' => 4]);
ProjectImage::create(['project_id' => $p->id, 'path' => $rel . 'missing.png', 'position' => 5]);   // file gone: must not crash
Post::create(['title' => 'P', 'slug' => 'p', 'body' => 'b', 'cover_path' => $rel . 'cover.png']);
$origBytes = filesize("$dir/big.png") + filesize("$dir/photo.jpg") + filesize("$dir/cover.png");
$opt = app(App\Services\ExistingImagesOptimizer::class);
$pending = $opt->pending();
$check('pending() counts only convertible files that exist', $pending['count'] === 3 && $pending['bytes'] >= $origBytes, json_encode($pending));
$dry = $opt->run(true);
$check('dry run changes nothing', file_exists("$dir/big.png") && ProjectImage::where('path', 'like', '%.png')->count() === 4 && count(array_filter($dry['log'], fn ($l) => str_starts_with($l, 'would convert'))) === 3);
$res = $opt->run();
$check('converted big.png, photo.jpg, cover.png (tiny.png ignored: too small to matter)', $res['converted'] === 3 && $res['kept'] === 0, json_encode($res));
$check('originals removed, webp created and smaller', !file_exists("$dir/big.png") && !file_exists("$dir/photo.jpg") && !file_exists("$dir/cover.png") && is_file("$dir/big.webp") && filesize("$dir/big.webp") < $origBytes / 10);
$check('both rows sharing a file point to the new webp', ProjectImage::where('path', $rel . 'big.webp')->count() === 2 && ProjectImage::where('path', $rel . 'big.png')->count() === 0);
$check('jpg row + post cover updated', ProjectImage::where('path', $rel . 'photo.webp')->exists() && Post::first()->cover_path === $rel . 'cover.webp');
$check('small / already-webp / missing files left alone', file_exists("$dir/tiny.png") && ProjectImage::where('path', $rel . 'tiny.png')->exists() && ProjectImage::where('path', $rel . 'missing.png')->exists());
$check('webp keeps a sane size (<=1600px wide)', getimagesize("$dir/big.webp")[0] === 1600);
$check('saved_bytes reported', $res['saved_bytes'] > $origBytes * 0.9);
// a big file that cannot shrink is remembered and not offered again
$noGain = "$dir/nogain.png"; file_put_contents($noGain, str_repeat('A', 200 * 1024)); ProjectImage::create(['project_id' => $p->id, 'path' => $rel . 'nogain.png', 'position' => 7]);
$before = $opt->pending()['count'];
$r2 = $opt->run();
$check('unconvertible big file is skipped, then no longer pending', $before === 1 && $r2['skipped'] === 1 && $opt->pending()['count'] === 0 && file_exists($noGain), json_encode([$before, $r2]));
file_put_contents($noGain, str_repeat('B', 300 * 1024));
$check('if that file changes it is offered again', $opt->pending()['count'] === 1);
@unlink($noGain); ProjectImage::where('path', $rel . 'nogain.png')->delete();
$again = $opt->run();
$check('second run finds nothing new', $again['converted'] === 0);

$ctl = $app->make(App\Http\Controllers\ProjectAdminController::class);
$mkimg("$dir/another.png", 'png'); ProjectImage::create(['project_id' => $p->id, 'path' => $rel . 'another.png', 'position' => 9]);
$req = Request::create('/admin/images/optimize', 'POST'); $req->setLaravelSession($app['session']->driver()); $app->instance('request', $req);
$resp = $ctl->optimizeImages($opt, app(App\Services\ImageOptimizer::class));
$check('admin action redirects with a success summary', $resp->getStatusCode() === 302 && str_contains((string) $req->session()->get('success'), 'Compressed 1 image'), (string) $req->session()->get('success'));
$req2 = Request::create('/admin/projects'); $req2->setLaravelSession($app['session']->driver()); $req2->session()->put('is_admin', true); $req2->session()->put('admin_last_seen', time());
$mkimg("$dir/more.png", 'png'); ProjectImage::create(['project_id' => $p->id, 'path' => $rel . 'more.png', 'position' => 10]);
$page = $kernel->handle($req2)->getContent();
$check('admin projects page offers "Optimize images" when something is pending', str_contains($page, 'can be compressed') && str_contains($page, 'Optimize images'));
$opt->run();
$page = $kernel->handle($req2)->getContent();
$check('banner disappears when nothing is pending', !str_contains($page, 'can be compressed'));

// cleanup
foreach (glob("$dir/*") as $f) @unlink($f); @rmdir($dir);
echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);

