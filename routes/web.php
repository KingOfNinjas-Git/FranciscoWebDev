<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProjectAdminController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Middleware\AdminAuth;

Route::get('/', [ProfileController::class, 'index'])->name('home');
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:5,10')->name('contact.send');

// Old multi-page URLs now land on their section of the one-page site
Route::get('/about', fn () => redirect('/#about'));
Route::get('/contact', fn () => redirect('/#contact'));
Route::get('/projects', fn () => redirect('/#projects'));
Route::get('/projects/{slug}', fn ($slug) => redirect('/#projects'));

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
Route::post('/admin/images/{id}/cover', [ProjectAdminController::class, 'coverImage'])->name('admin.images.cover');
Route::delete('/admin/images/{id}', [ProjectAdminController::class, 'destroyImage'])->name('admin.images.destroy');
});
