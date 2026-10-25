<?php
// Plan 1 fixes: responsive image variants + srcset, LCP hints, image caching, logo label, startup JS.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\{Request, UploadedFile}; use App\Models\{Post, Project, ProjectImage}; use App\Services\{ImageVariants, ImageOptimizer, ExistingImagesOptimizer};
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path));
$kb = fn (string $rel) => round(filesize(public_path($rel)) / 1024);

$dir = public_path('imgs/_perf_test'); @mkdir($dir, 0755, true);
$R = 'imgs/_perf_test/';
$photo = function (string $file, int $w, int $h, string $type = 'webp') {
    $im = imagecreatetruecolor($w, $h);
    for ($i = 0; $i < 1500; $i++) { $x = rand(0, $w - 30); $y = rand(0, $h - 30); imagefilledrectangle($im, $x, $y, $x + rand(5, 60), $y + rand(5, 60), imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255))); }
    match ($type) { 'png' => imagepng($im, $file, 1), 'jpg' => imagejpeg($im, $file, 95), default => imagewebp($im, $file, 90) };
    imagedestroy($im);
};

echo "== ImageVariants\n";
$photo("$dir/big.webp", 1600, 1000);
$check('variant names follow the original', ImageVariants::variantPath('imgs/a/b.webp', 480) === 'imgs/a/b-480.webp' && ImageVariants::variantPath('imgs/a/b.png', 960) === 'imgs/a/b-960.webp');
$check('missing() is true before, make() reports what it created', ImageVariants::missing($R . 'big.webp') && ImageVariants::make($R . 'big.webp') === [$R . 'big-480.webp', $R . 'big-960.webp']);
[$w480, $h480] = getimagesize("$dir/big-480.webp"); [$w960, $h960] = getimagesize("$dir/big-960.webp");
$check('variants are 480 and 960 wide with the same proportions', $w480 === 480 && $w960 === 960 && abs($h480 - 300) <= 1 && abs($h960 - 600) <= 1, "{$w480}x{$h480} / {$w960}x{$h960}");
$check('variants are much smaller files', filesize("$dir/big-480.webp") < filesize("$dir/big-960.webp") && filesize("$dir/big-960.webp") < filesize("$dir/big.webp") * 0.7, $kb($R . 'big-480.webp') . ' / ' . $kb($R . 'big-960.webp') . ' / ' . $kb($R . 'big.webp') . ' KB');
$check('missing() is false once they exist; make() is a no-op the second time', ! ImageVariants::missing($R . 'big.webp') && ImageVariants::make($R . 'big.webp') === []);
$srcset = ImageVariants::srcset($R . 'big.webp');
$check('srcset lists 480w, 960w and the original width', $srcset === asset($R . 'big-480.webp') . ' 480w, ' . asset($R . 'big-960.webp') . ' 960w, ' . asset($R . 'big.webp') . ' 1600w', (string) $srcset);
$check('thumb() is the 480 copy', ImageVariants::thumb($R . 'big.webp') === asset($R . 'big-480.webp'));
$check('size() reads the original dimensions', ImageVariants::size($R . 'big.webp') === [1600, 1000]);
$photo("$dir/mid.webp", 700, 400); $mid = ImageVariants::make($R . 'mid.webp');
$check('a 700px picture only gets the 480 copy', $mid === [$R . 'mid-480.webp'] && ! is_file("$dir/mid-960.webp") && str_contains(ImageVariants::srcset($R . 'mid.webp'), '480w') && ! str_contains(ImageVariants::srcset($R . 'mid.webp'), '960w'));
$photo("$dir/small.webp", 400, 300);
$check('a picture under 480px gets nothing: no srcset, thumb is the original, not "missing"', ImageVariants::make($R . 'small.webp') === [] && ImageVariants::srcset($R . 'small.webp') === null && ImageVariants::thumb($R . 'small.webp') === asset($R . 'small.webp') && ! ImageVariants::missing($R . 'small.webp'));
$check('unknown files are handled without errors', ImageVariants::make('imgs/_perf_test/nope.webp') === [] && ImageVariants::srcset('imgs/_perf_test/nope.webp') === null && ImageVariants::size('imgs/_perf_test/nope.webp') === null && ! ImageVariants::missing('imgs/_perf_test/nope.webp'));
$photo("$dir/shot.png", 1400, 900, 'png'); $photo("$dir/pic.jpg", 1400, 900, 'jpg');
$check('PNG and JPG originals get webp variants', count(ImageVariants::make($R . 'shot.png')) === 2 && is_file("$dir/shot-480.webp") && count(ImageVariants::make($R . 'pic.jpg')) === 2 && is_file("$dir/pic-960.webp"));
ImageVariants::delete($R . 'shot.png');
$check('delete() removes the variants only', ! is_file("$dir/shot-480.webp") && ! is_file("$dir/shot-960.webp") && is_file("$dir/shot.png"));

echo "== your real 570 KB cover image\n";
$live = @file_get_contents('https://franciscowebdev.com/imgs/blog/1792923958_accessibilitytogether.webp');
if ($live) {
    file_put_contents("$dir/real.webp", $live); [$rw, $rh] = getimagesize("$dir/real.webp");
    ImageVariants::make($R . 'real.webp');
    echo "         original {$rw}x{$rh}: " . $kb($R . 'real.webp') . " KB | 960 copy: " . $kb($R . 'real-960.webp') . " KB | 480 copy: " . $kb($R . 'real-480.webp') . " KB\n";
    $check('a phone card now downloads a fraction of the original', filesize("$dir/real-480.webp") < filesize("$dir/real.webp") * 0.25 && filesize("$dir/real-960.webp") < filesize("$dir/real.webp") * 0.6, $kb($R . 'real-480.webp') . ' KB');
} else { echo "         (live image not reachable, skipped)\n"; }

echo "== upload + delete keep variants in step\n";
$ctl = $app->make(App\Http\Controllers\ProjectAdminController::class);
$mk = function ($m = 'POST', $data = [], $files = []) use ($app) { $r = Request::create('/x', $m, $data, [], $files); $r->setLaravelSession($app['session']->driver()); $app->instance('request', $r); return $r; };
$proj = Project::create(['title' => 'Perf', 'slug' => 'perf-test']);
$tmp = sys_get_temp_dir() . '/upload_big.png'; $photo($tmp, 1800, 1100, 'png');
$ctl->storeImage($mk('POST', ['alt' => 'x'], ['image' => new UploadedFile($tmp, 'Upload Big.png', 'image/png', null, true)]), $proj->id, app(ImageOptimizer::class));
$img = $proj->images()->first();
$base = $img->public_path;
$check('uploading a project image writes the WebP plus its 480/960 versions', str_ends_with($base, '.webp') && is_file(public_path($base)) && is_file(public_path(ImageVariants::variantPath($base, 480))) && is_file(public_path(ImageVariants::variantPath($base, 960))), $base);
$ctl->destroyImage($img->id);
$check('deleting that image removes the original and both versions', ! is_file(public_path($base)) && ! is_file(public_path(ImageVariants::variantPath($base, 480))) && ! is_file(public_path(ImageVariants::variantPath($base, 960))));
@rmdir(public_path('imgs/projects/perf-test'));

$pctl = $app->make(App\Http\Controllers\PostAdminController::class);
$pctl->store($mk('POST', ['title' => 'Cover test', 'body' => 'Body text', 'status' => 'published'], ['cover' => new UploadedFile($tmp, 'cover.png', 'image/png', null, true)]), app(ImageOptimizer::class));
$post = Post::first(); $c1 = $post->cover_path;
$check('a post cover upload gets variants too', is_file(public_path(ImageVariants::variantPath($c1, 480))) && is_file(public_path(ImageVariants::variantPath($c1, 960))), $c1);
sleep(1);
$pctl->update($mk('PUT', ['title' => 'Cover test', 'body' => 'Body text', 'status' => 'published'], ['cover' => new UploadedFile($tmp, 'cover2.png', 'image/png', null, true)]), $post->id, app(ImageOptimizer::class));
$post->refresh(); $c2 = $post->cover_path;
$check('replacing the cover removes the old file and its variants, creates the new ones', $c2 !== $c1 && ! is_file(public_path($c1)) && ! is_file(public_path(ImageVariants::variantPath($c1, 480))) && is_file(public_path(ImageVariants::variantPath($c2, 960))));
$check('deleting the post removes the cover and its variants', (function () use ($pctl, $post, $c2) { $pctl->destroy($post->id); return ! is_file(public_path($c2)) && ! is_file(public_path(ImageVariants::variantPath($c2, 480))); })());
@unlink($tmp);

echo "== pictures that already exist\n";
$photo("$dir/old.png", 2000, 1300, 'png'); file_put_contents("$dir/old.png", file_get_contents("$dir/old.png") . str_repeat("\0", 120 * 1024));   // big enough to convert
$photo("$dir/ready.webp", 1500, 900);                                                                                                               // already WebP, no variants yet
$p2 = Project::create(['title' => 'Old', 'slug' => 'old-p']);
ProjectImage::create(['project_id' => $p2->id, 'path' => $R . 'old.png', 'position' => 0]);
ProjectImage::create(['project_id' => $p2->id, 'path' => $R . 'ready.webp', 'position' => 1]);
Post::create(['title' => 'Old cover', 'slug' => 'old-cover', 'body' => 'b', 'cover_path' => $R . 'ready.webp', 'status' => 'published', 'published_at' => now()->subDay()]);
$opt = app(ExistingImagesOptimizer::class);
$pend = $opt->pending();
$check('pending() counts the convertible PNG and the WebP missing its versions separately', $pend['count'] === 1 && $pend['variants'] === 1, json_encode($pend));
$dry = $opt->run(true);
$check('dry run touches nothing', is_file("$dir/old.png") && ! is_file("$dir/ready-480.webp") && $dry['variants'] === 0 && count($dry['log']) >= 2);
$res = $opt->run();
$check('run converts the PNG and makes versions for both pictures (shared files only once)', $res['converted'] === 1 && $res['variants'] === 2 && is_file("$dir/old-480.webp") && is_file("$dir/ready-960.webp") && ! is_file("$dir/old.png"), json_encode($res));
$check('database rows point at the new WebP', ProjectImage::where('path', $R . 'old.webp')->exists());
$check('nothing left to do afterwards', $opt->pending() === ['count' => 0, 'bytes' => 0, 'variants' => 0] && $opt->run()['variants'] === 0);
$req = $mk('POST'); $ctl->optimizeImages($opt, app(ImageOptimizer::class));
$check('admin button reports "nothing to optimize" when done', str_contains((string) $req->session()->get('success'), 'Nothing to optimize'), (string) $req->session()->get('success'));
$photo("$dir/later.webp", 1500, 900); ProjectImage::create(['project_id' => $p2->id, 'path' => $R . 'later.webp', 'position' => 2]);
$req = $mk('POST'); $ctl->optimizeImages($opt, app(ImageOptimizer::class));
$check('admin button message mentions the smaller phone-size versions', str_contains((string) $req->session()->get('success'), 'smaller phone-size versions of 1 image'), (string) $req->session()->get('success'));
$photo("$dir/banner.webp", 1500, 900); ProjectImage::create(['project_id' => $p2->id, 'path' => $R . 'banner.webp', 'position' => 3]);
$adm = Request::create('https://franciscowebdev.com/admin/projects'); $adm->setLaravelSession($app['session']->driver()); $adm->session()->put('is_admin', true); $adm->session()->put('admin_last_seen', time());
$html = $kernel->handle($adm)->getContent();
$check('admin banner offers the phone-size versions', str_contains($html, 'can get smaller phone-size versions') && str_contains($html, 'Optimize images'));

echo "== the pages\n";
Post::query()->delete(); Project::query()->delete(); ProjectImage::query()->delete();
$photo("$dir/c1.webp", 1600, 1000); $photo("$dir/c2.webp", 1600, 1000); ImageVariants::make($R . 'c1.webp'); ImageVariants::make($R . 'c2.webp');
foreach ([1 => 'c1', 2 => 'c2'] as $n => $f) { Post::create(['title' => "Post $n", 'slug' => "post-$n", 'excerpt' => 'x', 'body' => "## H\ntext", 'cover_path' => $R . "$f.webp", 'cover_alt' => "Cover $n", 'status' => 'published', 'published_at' => now()->subDays($n)]); }
Post::create(['title' => 'No cover', 'slug' => 'no-cover', 'body' => 'b', 'status' => 'published', 'published_at' => now()->subDays(9)]);
$h = $get('/blog')->getContent();
preg_match_all('/<img [^>]*>/', $h, $imgs); $cards = array_values(array_filter($imgs[0], fn ($t) => str_contains($t, '/imgs/_perf_test/')));
$check('blog cards use srcset + sizes', count($cards) === 2 && str_contains($cards[0], 'srcset="' . asset($R . 'c1-480.webp') . ' 480w') && str_contains($cards[0], 'sizes="(min-width: 1024px) 380px, (min-width: 768px) 45vw, 92vw"'), $cards[0] ?? 'none');
$check('first card is the likely LCP image: fetchpriority=high and NOT lazy', str_contains($cards[0], 'fetchpriority="high"') && ! str_contains($cards[0], 'loading="lazy"'));
$check('later cards stay lazy', str_contains($cards[1], 'loading="lazy"') && ! str_contains($cards[1], 'fetchpriority'));
$check('cards decode off the main thread', str_contains($cards[0], 'decoding="async"'));
$home = $get('/')->getContent();
preg_match_all('/<img [^>]*>/', $home, $himgs); $hcards = array_values(array_filter($himgs[0], fn ($t) => str_contains($t, '/imgs/_perf_test/')));
$check('home teaser cards are lazy (below the fold) but still use srcset', count($hcards) === 2 && ! str_contains($hcards[0], 'fetchpriority') && str_contains($hcards[0], 'loading="lazy"') && str_contains($hcards[0], 'srcset='));
$check('hero photo is prioritised', preg_match('/<img [^>]*Francisco\.jpg[^>]*fetchpriority="high"/', $home) === 1);
$post1 = $get('/blog/post-1')->getContent();
preg_match('/<img [^>]*c1\.webp[^>]*>/', $post1, $m); $cover = $m[0] ?? '';
$check('article cover: srcset, sizes, width/height (no layout shift), eager + high priority', str_contains($cover, 'srcset=') && str_contains($cover, 'sizes="(min-width: 768px) 768px, 100vw"') && str_contains($cover, 'width="1600"') && str_contains($cover, 'height="1000"') && str_contains($cover, 'fetchpriority="high"') && ! str_contains($cover, 'loading="lazy"') && str_contains($cover, 'h-auto'), $cover);
$check('og:image still points at the full image', str_contains($post1, 'property="og:image" content="' . asset($R . 'c1.webp') . '"'));
$check('a post without a cover renders as before', $get('/blog/no-cover')->getStatusCode() === 200);
$check('nothing breaks with no variants at all (srcset omitted, still a valid <img>)', (function () use ($photo, $dir, $R, $get) { $photo("$dir/novar.webp", 400, 300); Post::create(['title' => 'Plain', 'slug' => 'plain', 'body' => 'b', 'cover_path' => $R . 'novar.webp', 'status' => 'published', 'published_at' => now()]); $h = $get('/blog')->getContent(); preg_match('/<img [^>]*novar\.webp[^>]*>/', $h, $mm); return $mm && ! str_contains($mm[0], 'srcset') && str_contains($mm[0], 'src="' . asset($R . 'novar.webp') . '"'); })());
$pr = Project::create(['title' => 'Proj', 'slug' => 'proj', 'description' => 'D']); ProjectImage::create(['project_id' => $pr->id, 'path' => $R . 'c1.webp', 'position' => 0]); ProjectImage::create(['project_id' => $pr->id, 'path' => $R . 'c2.webp', 'position' => 1]);
$home = $get('/')->getContent();
preg_match('/<img [^>]*c1\.webp[^>]*alt=""[^>]*>/', $home, $pm);
$check('project card cover uses srcset', isset($pm[0]) && str_contains($pm[0], 'srcset="' . asset($R . 'c1-480.webp')) && str_contains($pm[0], 'loading="lazy"'), $pm[0] ?? 'none');
$page = $get('/projects/proj')->getContent(); preg_match('/<script type="application\/json" id="project-gallery-data">(.*?)<\/script>/s', $page, $pj); $data = json_decode(html_entity_decode($pj[1] ?? '[]'), true);
$check('gallery data on the project page carries a thumb for each image (480 copy)', ($data[0]['thumb'] ?? '') === asset($R . 'c1-480.webp') && ($data[1]['thumb'] ?? '') === asset($R . 'c2-480.webp') && str_contains($data[0]['url'], 'c1.webp'));
$check('logo link: accessible name contains its visible text', str_contains($home, 'aria-label="FC Francisco Cavaco"') && ! str_contains($home, 'aria-label="Francisco Cavaco"'));

echo "== scripts and server rules\n";
$js = file_get_contents('resources/js/site.js');
$check('gallery uses the small copy for backdrop and thumbnails', str_contains(file_get_contents('resources/js/gallery.js'), 'bg.src = image.thumb || image.url') && str_contains(file_get_contents('resources/js/gallery.js'), 't.src = image.thumb || image.url'));
$check('scroll-to-top first check waits for layout (no forced reflow at startup)', str_contains($js, 'requestAnimationFrame(update)') && ! preg_match('/\n    update\(\);\n/', $js));
$ht = file_get_contents('public/.htaccess');
$check('.htaccess: pictures cached 30 days, built files still 1 year, tags balanced', str_contains($ht, 'RewriteRule ^imgs/ - [E=PICTURE_CACHE:1]') && str_contains($ht, 'max-age=2592000" env=PICTURE_CACHE') && str_contains($ht, 'max-age=31536000, immutable" env=IMMUTABLE_ASSET') && substr_count($ht, '<IfModule') === substr_count($ht, '</IfModule>'));

foreach (glob("$dir/*") as $f) @unlink($f); @rmdir($dir);
echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
