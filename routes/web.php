<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserDb\CustLoginController;
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

// Kurzcode-Share-Link — anon-Login per teilbarem Link (7-stelliger Code,
// sessiondb.share_link), siehe MandantPwListController::edit() / CustLoginController.
Route::get('/s/{code}', [CustLoginController::class, 'loginViaShortCode'])
    ->name('login.shortcode')
    ->where('code', '[A-Za-z0-9]+');

// Honeypot-Routen aus storage/app/private/honeypot_paths.txt — ausserhalb jeder
// Auth-Middleware-Gruppe, muss ganz am Ende stehen (fungiert als Fallback fuer
// Koeder-Pfade wie wp-login.php, admin, phpmyadmin, .env etc.)
registerHoneypotRoutes();
