<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AdminSecurityController;
use App\Http\Controllers\PostAdminController;
use App\Http\Controllers\ProjectAdminController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\LeadAdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\StatsAdminController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\DecodeSafeFields;

// One page, three languages: / (English), /pt and /es
Route::get('/', [ProfileController::class, 'index'])->name('home');
Route::get('/pt', [ProfileController::class, 'index'])->defaults('locale', 'pt')->name('home.pt');
Route::get('/es', [ProfileController::class, 'index'])->defaults('locale', 'es')->name('home.es');
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');
Route::get('/sitemap.xml', [ProfileController::class, 'sitemap'])->name('sitemap');
Route::post('/e', [EventController::class, 'store'])->middleware('throttle:120,1')->name('event');
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:5,10')->name('contact.send');

// Blog (English at /blog, Portuguese at /pt/blog, Spanish at /es/blog)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed.xml', [BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
foreach (['pt', 'es'] as $blogLocale) {
    Route::get("/$blogLocale/blog", [BlogController::class, 'index'])->defaults('locale', $blogLocale)->name("blog.$blogLocale.index");
    Route::get("/$blogLocale/blog/{slug}", [BlogController::class, 'show'])->defaults('locale', $blogLocale)->name("blog.$blogLocale.show");
}

// Privacy notice (English at /privacy, Portuguese at /pt/privacy, Spanish at /es/privacy)
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
foreach (['pt', 'es'] as $pageLocale) {
    Route::get("/$pageLocale/privacy", [PageController::class, 'privacy'])->defaults('locale', $pageLocale)->name("privacy.$pageLocale");
}

// Used by uptime monitors and the self-check: 200 only when the site and its database work
Route::get('/health', function () {
    $checks = [
        'database' => (function () {
            try {
                \Illuminate\Support\Facades\DB::select('select 1');

                return true;
            } catch (\Throwable $e) {
                return false;
            }
        })(),
        'storage' => is_writable(storage_path('framework')) && is_writable(storage_path('logs')),
    ];
    $ok = ! in_array(false, $checks, true);

    return response()->json(['ok' => $ok] + $checks, $ok ? 200 : 503)->header('Cache-Control', 'no-store');
})->name('health');

// Proof of ownership for IndexNow (Bing and others): the page just contains the key
Route::get('/{key}.txt', function (string $key) {
    abort_unless(filled(config('portfolio.indexnow_key')) && hash_equals(config('portfolio.indexnow_key'), $key), 404);

    return response(config('portfolio.indexnow_key'), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->where('key', '[a-f0-9]{32}')->name('indexnow.key');

// Share pictures drawn for posts and projects that have no cover image
Route::get('/og/{locale}/{type}/{slug}.png', [OgImageController::class, 'show'])
    ->where(['locale' => 'en|pt|es', 'type' => 'blog|project', 'slug' => '[a-z0-9-]+'])->name('og.image');

// Accessibility statement (English at /accessibility, Portuguese at /pt/accessibility, Spanish at /es/accessibility)
Route::get('/accessibility', [PageController::class, 'accessibility'])->name('accessibility');
foreach (['pt', 'es'] as $a11yLocale) {
    Route::get("/$a11yLocale/accessibility", [PageController::class, 'accessibility'])->defaults('locale', $a11yLocale)->name("accessibility.$a11yLocale");
}

// Old multi-page URLs now land on their section of the one-page site
Route::get('/about', fn () => redirect('/#about'));
Route::get('/contact', fn () => redirect('/#contact'));
Route::get('/projects', fn () => redirect('/#projects'));

// Project pages (English at /projects/x, Portuguese at /pt/projects/x, Spanish at /es/projects/x)
Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
foreach (['pt', 'es'] as $projectLocale) {
    Route::get("/$projectLocale/projects/{slug}", [ProjectController::class, 'show'])->defaults('locale', $projectLocale)->name("projects.$projectLocale.show");
}

// Admin login/logout
Route::get('/admin/login', [AdminLoginController::class, 'index'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.post');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
// Second step of the login when two-factor is on
Route::get('/admin/two-factor', [AdminLoginController::class, 'challenge'])->name('admin.2fa');
Route::post('/admin/two-factor', [AdminLoginController::class, 'verify'])->middleware('throttle:10,1')->name('admin.2fa.verify');

Route::middleware([AdminAuth::class, DecodeSafeFields::class])->group(function(){
Route::get('/admin/projects', [ProjectAdminController::class, 'index'])->name('admin.projects.index');
Route::get('/admin/projects/create', [ProjectAdminController::class, 'create'])->name('admin.projects.create');
Route::post('/admin/projects', [ProjectAdminController::class, 'store'])->name('admin.projects.store');
Route::get('/admin/projects/{id}/edit', [ProjectAdminController::class, 'edit'])->name('admin.projects.edit');
Route::post('/admin/projects/{id}/images', [ProjectAdminController::class, 'storeImage'])->name('admin.projects.images.store');
Route::put('/admin/projects/{id}', [ProjectAdminController::class, 'update'])->name('admin.projects.update');
Route::delete('/admin/projects/{id}', [ProjectAdminController::class, 'destroy'])->name('admin.projects.destroy');
Route::post('/admin/projects/{id}/images/reorder', [ProjectAdminController::class, 'reorderImages'])->name('admin.images.reorder');
Route::put('/admin/images/{id}', [ProjectAdminController::class, 'updateImage'])->name('admin.images.update');
Route::post('/admin/images/optimize', [ProjectAdminController::class, 'optimizeImages'])->name('admin.images.optimize');
Route::post('/admin/images/{id}/cover', [ProjectAdminController::class, 'coverImage'])->name('admin.images.cover');
Route::delete('/admin/images/{id}', [ProjectAdminController::class, 'destroyImage'])->name('admin.images.destroy');

// Visit statistics
Route::get('/admin/stats', [StatsAdminController::class, 'index'])->name('admin.stats');
Route::post('/admin/stats/report', [StatsAdminController::class, 'sendReport'])->middleware('throttle:3,1')->name('admin.stats.report');

// Contact form leads
Route::get('/admin/leads', [LeadAdminController::class, 'index'])->name('admin.leads.index');
Route::get('/admin/leads/{id}', [LeadAdminController::class, 'show'])->whereNumber('id')->name('admin.leads.show');
Route::put('/admin/leads/{id}', [LeadAdminController::class, 'update'])->whereNumber('id')->name('admin.leads.update');
Route::post('/admin/leads/{id}/reply', [LeadAdminController::class, 'reply'])->whereNumber('id')->middleware('throttle:10,1')->name('admin.leads.reply');
Route::delete('/admin/leads/{id}', [LeadAdminController::class, 'destroy'])->whereNumber('id')->name('admin.leads.destroy');

// Admin security (two-factor login) and backups
Route::get('/admin/security', [AdminSecurityController::class, 'index'])->name('admin.security');
Route::post('/admin/security/password', [AdminSecurityController::class, 'changePassword'])->middleware('throttle:5,1')->name('admin.security.password');
Route::post('/admin/security/logout-everywhere', [AdminSecurityController::class, 'logoutEverywhere'])->middleware('throttle:5,1')->name('admin.security.logout-all');
Route::post('/admin/security/two-factor/start', [AdminSecurityController::class, 'startSetup'])->name('admin.security.start');
Route::post('/admin/security/two-factor/cancel', [AdminSecurityController::class, 'cancelSetup'])->name('admin.security.cancel');
Route::post('/admin/security/two-factor/enable', [AdminSecurityController::class, 'enable'])->middleware('throttle:10,1')->name('admin.security.enable');
Route::post('/admin/security/two-factor/disable', [AdminSecurityController::class, 'disable'])->middleware('throttle:10,1')->name('admin.security.disable');
Route::post('/admin/security/recovery-codes', [AdminSecurityController::class, 'newRecoveryCodes'])->middleware('throttle:10,1')->name('admin.security.recovery');
Route::post('/admin/backup', [AdminSecurityController::class, 'backup'])->middleware('throttle:6,1')->name('admin.backup');

// Blog admin + AI drafting
Route::get('/admin/posts', [PostAdminController::class, 'index'])->name('admin.posts.index');
Route::get('/admin/posts/create', [PostAdminController::class, 'create'])->name('admin.posts.create');
Route::post('/admin/posts', [PostAdminController::class, 'store'])->name('admin.posts.store');
Route::post('/admin/posts/preview', [PostAdminController::class, 'preview'])->name('admin.posts.preview');
Route::get('/admin/posts/{id}/edit', [PostAdminController::class, 'edit'])->name('admin.posts.edit');
Route::put('/admin/posts/{id}', [PostAdminController::class, 'update'])->name('admin.posts.update');
Route::delete('/admin/posts/{id}', [PostAdminController::class, 'destroy'])->name('admin.posts.destroy');
// Requests that start (and cost) an AI call are limited; the daily cap in config/ai.php applies on top
Route::middleware('throttle:20,1')->group(function () {
    Route::post('/admin/ai/context', [AiController::class, 'context'])->name('admin.ai.context');
    Route::post('/admin/ai/draft', [AiController::class, 'draft'])->name('admin.ai.draft');
    Route::post('/admin/ai/translate', [AiController::class, 'translate'])->name('admin.ai.translate');
    Route::post('/admin/ai/alt', [AiController::class, 'altText'])->name('admin.ai.alt');
});
// The browser polls this every 2 seconds while a draft is being written, so it gets a much higher limit
Route::get('/admin/ai/{id}', [AiController::class, 'status'])->whereNumber('id')->middleware('throttle:240,1')->name('admin.ai.status');
});
