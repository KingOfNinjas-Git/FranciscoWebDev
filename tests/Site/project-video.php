<?php
// Optional demo video of a project: admin upload / replace / remove, and the public player.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);


use Illuminate\Http\{Request, UploadedFile}; use App\Models\Project; use App\Http\Controllers\ProjectAdminController;
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path))->getContent();
$mk = function ($m, $files = []) use ($app) { $r = Request::create('/x', $m, [], [], $files); $r->setLaravelSession($app['session']->driver()); $app->instance('request', $r); return $r; };
$mp4 = tempnam(sys_get_temp_dir(), 'v'); file_put_contents($mp4, "\0\0\0\x20ftypisom\0\0\2\0isomiso2avc1mp41" . str_repeat("\0", 2048));
$up = function ($name) use ($mp4) { $t = tempnam(sys_get_temp_dir(), 'u'); copy($mp4, $t); return new UploadedFile($t, $name, 'video/mp4', null, true); };
$txt = tempnam(sys_get_temp_dir(), 't'); file_put_contents($txt, 'not a video');

$p = Project::create(['title' => 'Vid', 'slug' => 'vid-test', 'description' => 'd']);
$ctl = $app->make(ProjectAdminController::class);

echo "== no video\n";
$check('page has no player without a video', ! str_contains($get('/projects/vid-test'), '<video'));

echo "== upload\n";
try { $ctl->storeVideo($mk('POST', ['video' => new UploadedFile($txt, 'x.mp4', 'video/mp4', null, true)]), $p->id); $rejected = false; } catch (Illuminate\Validation\ValidationException $e) { $rejected = true; }
$check('a text file named .mp4 is refused', $rejected);
$ctl->storeVideo($mk('POST', ['video' => $up('demo.mp4')]), $p->id);
$p->refresh();
$check('video saved under public/videos/projects/<slug>/', str_starts_with((string) $p->demo_video, 'videos/projects/vid-test/demo-') && is_file(public_path($p->demo_video)), (string) $p->demo_video);
$old = $p->demo_video;

echo "== public page\n";
foreach (['' => 'Demo video', '/pt' => 'Vídeo de demonstração', '/es' => 'Vídeo de demostración'] as $prefix => $label) {
    $h = $get("$prefix/projects/vid-test");
    $check(($prefix ?: '/en') . ': player with controls, no autoplay, accessible name', str_contains($h, '<video controls muted playsinline preload="metadata"') && ! str_contains($h, 'autoplay') && str_contains($h, 'aria-label="' . $label . ': Vid"') && str_contains($h, 'type="video/mp4"') && str_contains($h, $old), substr($h, strpos($h, '<video') ?: 0, 300));
}

echo "== replace and remove\n";
$ctl->storeVideo($mk('POST', ['video' => $up('again.mp4')]), $p->id);
$p->refresh();
$check('replacing deletes the old file', $p->demo_video !== $old && ! is_file(public_path($old)) && is_file(public_path($p->demo_video)));
$new = $p->demo_video;
$ctl->destroyVideo($p->id);
$p->refresh();
$check('removing clears the column and the file', $p->demo_video === null && ! is_file(public_path($new)));
$check('page is back to no player', ! str_contains($get('/projects/vid-test'), '<video'));

$ctl->storeVideo($mk('POST', ['video' => $up('last.mp4')]), $p->id);
$path = $p->fresh()->demo_video;
$ctl->destroy($p->id);
$check('deleting the project deletes its video file', ! is_file(public_path($path)));

@rmdir(public_path('videos/projects/vid-test')); @rmdir(public_path('videos/projects')); @rmdir(public_path('videos'));
@unlink($mp4); @unlink($txt);
echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
