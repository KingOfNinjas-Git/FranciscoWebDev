<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/', [ProfileController::class, 'index'])->name('home');
Route::get('/about', [ProfileController::class, 'about'])->name('about');
Route::get('/projects', [ProfileController::class, 'projects'])->name('projects');