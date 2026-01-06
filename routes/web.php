<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectAdminController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Middleware\AdminAuth;

Route::get('/', [ProfileController::class, 'index'])->name('home');
Route::get('/about', [ProfileController::class, 'about'])->name('about');
Route::get('/contact', [ProfileController::class, 'contact'])->name('contact');
Route::get('/projects', [ProfileController::class, 'projects'])->name('projects');
Route::get('/projects/{slug}', [ProfileController::class, 'projectShow'])->name('projects.show');

// Admin login/logout (no auth required)
Route::get('/admin/login', [AdminLoginController::class, 'index'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// Protected admin routes (using middleware class directly)
Route::middleware([AdminAuth::class])->group(function(){
Route::get('/admin/projects', [ProjectAdminController::class, 'index'])->name('admin.projects.index');
Route::get('/admin/projects/{id}/edit', [ProjectAdminController::class, 'edit'])->name('admin.projects.edit');
Route::post('/admin/projects/{id}/images', [ProjectAdminController::class, 'storeImage'])->name('admin.projects.images.store');
Route::put('/admin/projects/{id}', [ProjectAdminController::class, 'update'])->name('admin.projects.update');
Route::delete('/admin/images/{id}', [ProjectAdminController::class, 'destroyImage'])->name('admin.images.destroy');
});