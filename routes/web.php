<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserDb\SystemLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()
        ->view('auth.login-modal')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// System-Login — kein Link, kein Modal; URL nur dem System-User bekannt
Route::get(config('app.backstage_path'), [SystemLoginController::class, 'login'])
    ->name('system.backstage.login');
Route::post(config('app.backstage_path'), [SystemLoginController::class, 'handleLogin'])
    ->name('system.backstage.handle');
Route::post(config('app.backstage_path') . '/verify', [SystemLoginController::class, 'verifyTwoFactor'])
    ->name('system.login.verify');
