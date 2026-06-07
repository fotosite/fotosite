<?php

use App\Http\Controllers\Passkey\MandPasskeyController;
use App\Http\Controllers\SessionDb\MandantPwListController;
use App\Http\Controllers\UserDb\MandantCustController;
use App\Http\Controllers\UserDb\MandantDashboardController;
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
    Route::get('/login/passkey/options', [MandantLoginController::class, 'passkeyOptions'])
        ->name('login.passkey.options');
    Route::post('/login/passkey',        [MandantLoginController::class, 'passkeyLogin'])
        ->name('login.passkey');
});

// ── Authenticated area ────────────────────────────────────────
Route::middleware(['web', 'role:mand'])->prefix('mandant')->name('mandant.')->group(function () {
    Route::get('/dashboard', [MandantDashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/konto',            [MandantSelfController::class, 'edit'])
        ->name('konto');
    Route::patch('/konto',          [MandantSelfController::class, 'update'])
        ->name('konto.update');
    Route::patch('/konto/passwort', [MandantSelfController::class, 'updatePassword'])
        ->name('konto.password');

    Route::get('/passwortliste',             [MandantPwListController::class, 'edit'])
        ->name('pwlist');
    Route::patch('/passwortliste',           [MandantPwListController::class, 'update'])
        ->name('pwlist.update');
    Route::post('/passwortliste/pruefen',    [MandantPwListController::class, 'checkPassword'])
        ->name('pwlist.check');

    Route::get('/kunden',                    [MandantCustController::class, 'index'])
        ->name('kunden.index');
    Route::get('/kunden/einladen',           [MandantCustController::class, 'invite'])
        ->name('kunden.invite');
    Route::post('/kunden/einladen',          [MandantCustController::class, 'store'])
        ->name('kunden.store');
    Route::patch('/kunden/{id}/passcode',    [MandantCustController::class, 'update'])
        ->name('kunden.passcode');
    Route::delete('/kunden/{id}',            [MandantCustController::class, 'destroy'])
        ->name('kunden.destroy');

    Route::get('/passkeys',                  [MandPasskeyController::class, 'index'])
        ->name('passkeys');
    Route::get('/passkeys/register/options', [MandPasskeyController::class, 'registrationOptions'])
        ->name('passkeys.options');
    Route::post('/passkeys/register',        [MandPasskeyController::class, 'register'])
        ->name('passkeys.register');
    Route::patch('/passkeys/{id}/rename',    [MandPasskeyController::class, 'rename'])
        ->name('passkeys.rename');
    Route::delete('/passkeys/{id}',          [MandPasskeyController::class, 'destroy'])
        ->name('passkeys.destroy');
    Route::post('/passkeys/dismiss',         [MandPasskeyController::class, 'dismiss'])
        ->name('passkeys.dismiss');
});
