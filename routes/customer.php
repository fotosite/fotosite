<?php

use App\Http\Controllers\Passkey\CustPasskeyController;
use App\Http\Controllers\UserDb\CustDashboardController;
use App\Http\Controllers\UserDb\CustLoginController;
use App\Http\Controllers\UserDb\CustPasswordResetController;
use App\Http\Controllers\UserDb\CustRegisterController;
use App\Http\Controllers\UserDb\CustSelfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Area Routes  (/customer/*)
|--------------------------------------------------------------------------
| Routes for the customer-facing area.
| Mix of public and authenticated routes.
|
| Controllers live in:
|   App\Http\Controllers\UserDb\    — customer profile & auth
|   App\Http\Controllers\FotoDB\    — browsing photos
|   App\Http\Controllers\FotoBlobDb\— downloading/streaming media
*/

Route::middleware('web')->prefix('customer')->name('customer.')->group(function () {

    // ── Login / Logout ────────────────────────────────────
    Route::get('/login',          [CustLoginController::class, 'showLogin'])
        ->name('login');
    Route::post('/login',         [CustLoginController::class, 'handleLogin'])
        ->middleware('throttle:cust-login')
        ->name('login.handle');
    Route::post('/login/anon',    [CustLoginController::class, 'handleAnonLogin'])
        ->middleware('throttle:cust-anon-login')
        ->name('login.anon');
    Route::get('/login/passkey/options', [CustLoginController::class, 'passkeyOptions'])
        ->name('login.passkey.options');
    Route::post('/login/passkey',        [CustLoginController::class, 'passkeyLogin'])
        ->name('login.passkey');
    Route::get('/login/2fa',      [CustLoginController::class, 'showTwoFactor'])
        ->name('login.2fa');
    Route::post('/login/2fa',     [CustLoginController::class, 'verifyTwoFactor'])
        ->middleware('throttle:login-2fa')
        ->name('login.2fa.verify');
    Route::post('/logout',        [CustLoginController::class, 'logout'])
        ->name('logout');

    // ── Dashboard ─────────────────────────────────────────
    Route::get('/dashboard', [CustDashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/content', [CustDashboardController::class, 'content'])
        ->name('content');

    Route::get('/register/{token}',  [CustRegisterController::class, 'show'])
        ->name('register');
    Route::post('/register/{token}', [CustRegisterController::class, 'store'])
        ->name('register.store');

    // ── Passwort zurücksetzen ─────────────────────────────
    Route::get('/password-reset',          [CustPasswordResetController::class, 'showResetRequest'])
        ->name('password.reset.request');
    Route::post('/password-reset',         [CustPasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:password-reset')
        ->name('password.reset.send');
    Route::get('/password-reset/{token}',  [CustPasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/password-reset/{token}', [CustPasswordResetController::class, 'handleReset'])
        ->name('password.reset.handle');

    // ── Konto-Eigenverwaltung ─────────────────────────────
    Route::get('/konto',          [CustSelfController::class, 'edit'])
        ->name('konto');
    Route::patch('/konto',        [CustSelfController::class, 'update'])
        ->name('konto.update');
    Route::patch('/konto/password', [CustSelfController::class, 'updatePassword'])
        ->name('konto.password');
    Route::delete('/konto',       [CustSelfController::class, 'deleteAccount'])
        ->name('konto.delete');

    // ── Galerien-Verwaltung ───────────────────────────────
    Route::get('/galerien', [CustSelfController::class, 'galerien'])
        ->name('galerien');
    Route::post('/galerien/save-settings', [CustSelfController::class, 'saveSettings'])
        ->name('galerien.save-settings');
    Route::patch('/galerien/{pcodeId}/reorder/{direction}',
        [CustSelfController::class, 'reorderGalerie'])
        ->name('galerien.reorder')
        ->where('direction', 'up|down');
    Route::delete('/galerien/{pcodeId}',
        [CustSelfController::class, 'removeGalerie'])
        ->name('galerien.remove');

    // ── Passkey-Verwaltung (authentifiziert, role:cust folgt) ─
    Route::get('/passkeys',                  [CustPasskeyController::class, 'index'])
        ->name('passkeys');
    Route::get('/passkeys/register/options', [CustPasskeyController::class, 'registrationOptions'])
        ->name('passkeys.options');
    Route::post('/passkeys/register',        [CustPasskeyController::class, 'register'])
        ->name('passkeys.register');
    Route::patch('/passkeys/{id}/rename',    [CustPasskeyController::class, 'rename'])
        ->name('passkeys.rename');
    Route::delete('/passkeys/{id}',          [CustPasskeyController::class, 'destroy'])
        ->name('passkeys.destroy');
    Route::post('/passkeys/dismiss',         [CustPasskeyController::class, 'dismiss'])
        ->name('passkeys.dismiss');
});
