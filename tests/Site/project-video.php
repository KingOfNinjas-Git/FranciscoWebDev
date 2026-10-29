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
$mk = function ($m, $files = [], $data = []) use ($app) { $r = Request::create('/x', $m, $data, [], $files); $r->setLaravelSession($app['session']->driver()); $app->instance('request', $r); return $r; };
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
$ctl->destroyVideo($mk('DELETE'), $p->id);
$p->refresh();
$check('removing clears the column and the file', $p->demo_video === null && ! is_file(public_path($new)));
$check('page is back to no player', ! str_contains($get('/projects/vid-test'), '<video'));

echo "== second format and the written version\n";
$ctl->storeVideo($mk('POST', ['video' => $up('main.mp4')]), $p->id);
$ctl->storeVideo($mk('POST', ['video' => $up('second.mp4')], ['slot' => 'second']), $p->id);
$p->refresh();
$check('the second slot is saved next to the main video, not over it', filled($p->demo_video) && filled($p->demo_video_fallback) && $p->demo_video !== $p->demo_video_fallback && is_file(public_path($p->demo_video_fallback)));
$firstMain = $p->demo_video;
$p->update(['demo_video' => 'videos/projects/vid-test/demo-aaaa1111.webm']); // pretend the main one is the WebM
$h = $get('/projects/vid-test');
$mp4At = strpos($h, 'type="video/mp4"'); $webmAt = strpos($h, 'type="video/webm"');
$check('both sources are offered, MP4 first (it plays on every phone)', $mp4At !== false && $webmAt !== false && $mp4At < $webmAt, substr($h, strpos($h, '<video') ?: 0, 500));
$p->update(['demo_video' => $firstMain]);
$check('no written version, no text box', ! str_contains($get('/projects/vid-test'), 'Text version of this video'));
$p->update(['video_description' => "First, the home screen.\n\nThen a habit is ticked.", 'translations' => ['pt' => ['video_description' => 'Primeiro, o ecrã inicial.']]]);
$en = $get('/projects/vid-test'); $pt = $get('/pt/projects/vid-test'); $es = $get('/es/projects/vid-test');
$check('English: the written version sits under the video, in paragraphs', str_contains($en, 'Text version of this video') && str_contains($en, '<p>First, the home screen.</p>') && str_contains($en, '<p>Then a habit is ticked.</p>'));
$check('Portuguese: translated label and text', str_contains($pt, 'Versão em texto deste vídeo') && str_contains($pt, 'Primeiro, o ecrã inicial.'));
$check('Spanish: translated label, English text as the fallback', str_contains($es, 'Versión en texto de este vídeo') && str_contains($es, 'First, the home screen.'));
$ctl->destroyVideo($mk('DELETE', [], ['slot' => 'second']), $p->id);
$p->refresh();
$check('removing the second slot keeps the main video', $p->demo_video_fallback === null && is_file(public_path($p->demo_video)));
$ctl->storeVideo($mk('POST', ['video' => $up('second.mp4')], ['slot' => 'second']), $p->id);
$p->refresh();
$path = $p->demo_video;
$path2 = $p->demo_video_fallback;
$ctl->destroy($p->id);
$check('deleting the project deletes both video files', ! is_file(public_path($path)) && ! is_file(public_path($path2)));

@rmdir(public_path('videos/projects/vid-test')); @rmdir(public_path('videos/projects')); @rmdir(public_path('videos'));
@unlink($mp4); @unlink($txt);
echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
