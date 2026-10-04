<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaptureImageController;
use App\Http\Controllers\DevotionalPhotoController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\Editor\DevotionalController;
use App\Http\Controllers\Editor\WorkspaceController;
use App\Http\Controllers\Editor\SummaryController;
use App\Http\Controllers\Owner\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : redirect()->route('login');
});

// ---- authentication ----------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')->name('login.attempt');

    // Sign in with Google.
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Devotional photo, served only to its uploader or an owner (private disk).
Route::get('/devotional/{devotional}/photo', [DevotionalPhotoController::class, 'show'])
    ->middleware('auth')->name('devotional.photo');

// Screen capture image, served only to its editor or an owner (private disk).
Route::get('/captures/{capture}/image', [CaptureImageController::class, 'show'])
    ->middleware('auth')->name('capture.image');

// ---- role home ---------------------------------------------------------
Route::get('/home', function () {
    return auth()->user()->isOwner()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('workspace');
})->middleware('auth')->name('home');

// ---- video editor ------------------------------------------------------
Route::middleware(['auth', 'role:Video Editor'])->group(function () {
    Route::get('/devotional', [DevotionalController::class, 'show'])->name('devotional');
    Route::get('/workspace', [WorkspaceController::class, 'show'])->name('workspace');
    Route::get('/summary', [SummaryController::class, 'show'])->name('summary');
});

// ---- owner / administrator --------------------------------------------
Route::middleware(['auth', 'role:Owner'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'show'])->name('admin.dashboard');
});
