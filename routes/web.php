<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\PostAdminController;
use App\Http\Controllers\ProjectAdminController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Middleware\AdminAuth;

// One page, three languages: / (English), /pt and /es
Route::get('/', [ProfileController::class, 'index'])->name('home');
Route::get('/pt', [ProfileController::class, 'index'])->defaults('locale', 'pt')->name('home.pt');
Route::get('/es', [ProfileController::class, 'index'])->defaults('locale', 'es')->name('home.es');
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');
Route::get('/sitemap.xml', [ProfileController::class, 'sitemap'])->name('sitemap');
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:5,10')->name('contact.send');

// Blog (English at /blog, Portuguese at /pt/blog, Spanish at /es/blog)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed.xml', [BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
foreach (['pt', 'es'] as $blogLocale) {
    Route::get("/$blogLocale/blog", [BlogController::class, 'index'])->defaults('locale', $blogLocale)->name("blog.$blogLocale.index");
    Route::get("/$blogLocale/blog/{slug}", [BlogController::class, 'show'])->defaults('locale', $blogLocale)->name("blog.$blogLocale.show");
}

// Old multi-page URLs now land on their section of the one-page site
Route::get('/about', fn () => redirect('/#about'));
Route::get('/contact', fn () => redirect('/#contact'));
Route::get('/projects', fn () => redirect('/#projects'));
Route::get('/projects/{slug}', fn ($slug) => redirect('/#projects/' . $slug));

// Admin login/logout
Route::get('/admin/login', [AdminLoginController::class, 'index'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.post');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

Route::middleware([AdminAuth::class])->group(function(){
Route::get('/admin/projects', [ProjectAdminController::class, 'index'])->name('admin.projects.index');
Route::get('/admin/projects/create', [ProjectAdminController::class, 'create'])->name('admin.projects.create');
Route::post('/admin/projects', [ProjectAdminController::class, 'store'])->name('admin.projects.store');
Route::get('/admin/projects/{id}/edit', [ProjectAdminController::class, 'edit'])->name('admin.projects.edit');
Route::post('/admin/projects/{id}/images', [ProjectAdminController::class, 'storeImage'])->name('admin.projects.images.store');
Route::put('/admin/projects/{id}', [ProjectAdminController::class, 'update'])->name('admin.projects.update');
Route::delete('/admin/projects/{id}', [ProjectAdminController::class, 'destroy'])->name('admin.projects.destroy');
Route::post('/admin/projects/{id}/images/reorder', [ProjectAdminController::class, 'reorderImages'])->name('admin.images.reorder');
Route::put('/admin/images/{id}', [ProjectAdminController::class, 'updateImage'])->name('admin.images.update');
Route::post('/admin/images/{id}/cover', [ProjectAdminController::class, 'coverImage'])->name('admin.images.cover');
Route::delete('/admin/images/{id}', [ProjectAdminController::class, 'destroyImage'])->name('admin.images.destroy');

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
});
// The browser polls this every 2 seconds while a draft is being written, so it gets a much higher limit
Route::get('/admin/ai/{id}', [AiController::class, 'status'])->whereNumber('id')->middleware('throttle:240,1')->name('admin.ai.status');
});
