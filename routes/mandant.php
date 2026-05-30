<?php

use App\Http\Controllers\SessionDb\MandantPwListController;
use App\Http\Controllers\UserDb\MandantLoginController;
use App\Http\Controllers\UserDb\MandantSelfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mandant Area Routes  (/mandant/*)
|--------------------------------------------------------------------------
| Routes for the mandant (tenant) management area.
| Access is restricted to authenticated mandant users.
|
| Controllers live in:
|   App\Http\Controllers\UserDb\    — mandant user management
|   App\Http\Controllers\FotoDB\    — mandant photo management
|   App\Http\Controllers\FotoBlobDb\— mandant media management
*/

// ── Public login routes (no role middleware) ──────────────────
Route::middleware('web')->prefix('mandant')->name('mandant.')->group(function () {
    Route::get('/login',       [MandantLoginController::class, 'showLogin'])
        ->name('login');
    Route::post('/login',      [MandantLoginController::class, 'handleLogin'])
        ->name('login.handle');
    Route::get('/login/2fa',   [MandantLoginController::class, 'showTwoFactor'])
        ->name('login.2fa');
    Route::post('/login/2fa',  [MandantLoginController::class, 'verifyTwoFactor'])
        ->name('login.2fa.verify');
    Route::post('/logout',     [MandantLoginController::class, 'logout'])
        ->name('logout');
});

// ── Authenticated area ────────────────────────────────────────
Route::middleware(['web', 'role:mand'])->prefix('mandant')->name('mandant.')->group(function () {
    Route::get('/dashboard', fn() => view('mandant.dashboard'))
        ->name('dashboard');

    Route::get('/konto',            [MandantSelfController::class, 'edit'])
        ->name('konto');
    Route::patch('/konto',          [MandantSelfController::class, 'update'])
        ->name('konto.update');
    Route::patch('/konto/passwort', [MandantSelfController::class, 'updatePassword'])
        ->name('konto.password');

    Route::get('/passwortliste',   [MandantPwListController::class, 'edit'])
        ->name('pwlist');
    Route::patch('/passwortliste', [MandantPwListController::class, 'update'])
        ->name('pwlist.update');
});
