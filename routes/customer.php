<?php

use App\Http\Controllers\Passkey\CustPasskeyController;
use App\Http\Controllers\UserDb\CustDashboardController;
use App\Http\Controllers\UserDb\CustLoginController;
use App\Http\Controllers\UserDb\CustRegisterController;
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
        ->middleware('throttle:10,1')
        ->name('login.handle');
    Route::post('/login/anon',    [CustLoginController::class, 'handleAnonLogin'])
        ->middleware('throttle:5,1')
        ->name('login.anon');
    Route::get('/login/passkey/options', [CustLoginController::class, 'passkeyOptions'])
        ->name('login.passkey.options');
    Route::post('/login/passkey',        [CustLoginController::class, 'passkeyLogin'])
        ->name('login.passkey');
    Route::get('/login/2fa',      [CustLoginController::class, 'showTwoFactor'])
        ->name('login.2fa');
    Route::post('/login/2fa',     [CustLoginController::class, 'verifyTwoFactor'])
        ->middleware('throttle:3,10')
        ->name('login.2fa.verify');
    Route::post('/logout',        [CustLoginController::class, 'logout'])
        ->name('logout');

    // ── Dashboard ─────────────────────────────────────────
    Route::get('/dashboard', [CustDashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/register/{token}',  [CustRegisterController::class, 'show'])
        ->name('register');
    Route::post('/register/{token}', [CustRegisterController::class, 'store'])
        ->name('register.store');

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
});
