<?php

use App\Http\Controllers\DatenschutzController;
use App\Http\Controllers\Passkey\CustPasskeyController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\UserDb\CustDashboardController;
use App\Http\Controllers\UserDb\CustLoginController;
use App\Http\Controllers\UserDb\CustPasswordResetController;
use App\Http\Controllers\UserDb\CustRegisterController;
use App\Http\Controllers\UserDb\CustSelfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Area Routes  (/customer/*)                    VERSION: 1.5.0
|--------------------------------------------------------------------------
| Routes for the customer-facing area.
| Mix of public and authenticated routes.
|
| Controllers live in:
|   App\Http\Controllers\UserDb\    — customer profile & auth
|   App\Http\Controllers\FotoDB\    — browsing photos
|   App\Http\Controllers\FotoBlobDb\— downloading/streaming media
|
| CHANGES: 1.1.0 (2026-06-16) datenschutz-Routen in eigene Group ohne
|          App-spezifische Custom-Middleware (SessionHijackProtection,
|          SessionIdleTimeout, ValidateUserExists) — öffentlich erreichbar
|          ohne Login, für Einladungsempfänger, anon, cust, mand.
|          1.2.0 (2026-06-16) URI-Segment /datenschutz/ → /ds/ (mod_security
|          auf Alfahosting blockt "datenschutz" in URLs mit 403). Route-Namen
|          customer.datenschutz.* bleiben unverändert.
|          1.3.0 (2026-06-16) withoutMiddleware-Konstrukt entfernt — verursacht
|          403 in Laravel 13, weil Custom-Middlewares via web(append:[]) eingehängt
|          sind und sich so nicht per withoutMiddleware() auf Gruppe ausschließen
|          lassen. ds-Routen jetzt in der normalen web-Group; alle drei Middlewares
|          haben eigenständige Passthroughs für unangemeldete Erstbesucher.
|          1.4.0 (2026-06-18) konto.passwort (POST, PW-Modal), konto.email-aendern
|          (POST) und konto.email-bestaetigen/{token} (GET) ergänzt — E-Mail-Aenderung
|          per Bestaetigungslink, siehe CustSelfController.
|          1.5.0 (2026-06-18) policy.update (GET) / policy.confirm (POST) ergänzt —
|          blockierendes Popup bei veralteter DS-/Upload-Policy-Version, siehe
|          App\Http\Middleware\CheckPolicyVersion und PolicyController.
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
    Route::post('/konto/passwort', [CustSelfController::class, 'updatePassword'])
        ->name('konto.passwort');
    Route::post('/konto/email-aendern', [CustSelfController::class, 'requestEmailChange'])
        ->name('konto.email-aendern');
    Route::get('/konto/email-bestaetigen/{token}', [CustSelfController::class, 'confirmEmailChange'])
        ->name('konto.email-bestaetigen');
    Route::delete('/konto',       [CustSelfController::class, 'deleteAccount'])
        ->name('konto.delete');

    // ── Policy-Update (DS/Upload-Hinweis) — CheckPolicyVersion schliesst sich
    //    selbst per routeIs('*.policy.*') aus, um Redirect-Schleifen zu
    //    vermeiden ──────────────────────────────────────────────────────
    Route::get('/policy-update',  [PolicyController::class, 'showCust'])
        ->name('policy.update');
    Route::post('/policy-update', [PolicyController::class, 'confirmCust'])
        ->name('policy.confirm');

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

    // ── Datenschutz — öffentlich ──────────────────────────
    // Erreichbar ohne Login: Einladungsempfänger, anon, cust, mand.
    // URI /ds/ statt /datenschutz/ — mod_security auf Alfahosting
    // blockt URLs mit "datenschutz" (403). Route-Namen: datenschutz.*.
    // Die drei App-Custom-Middlewares (SessionHijackProtection,
    // SessionIdleTimeout, ValidateUserExists) haben eigenständige
    // Passthroughs für Erstbesucher ohne Session — kein withoutMiddleware
    // nötig (und withoutMiddleware() auf Group ist in Laravel 13 mit
    // web(append:[...]) ohnehin broken → 403).
    Route::get('/ds/erlaeuterung', [DatenschutzController::class, 'erlaeuterung'])
        ->name('datenschutz.erlaeuterung');
    Route::get('/ds/erklaerung-pdf', [DatenschutzController::class, 'erklaerungPdf'])
        ->name('datenschutz.erklaerung-pdf');
    Route::get('/ds/upload-bedingungen-pdf', [DatenschutzController::class, 'uploadBedingungenPdf'])
        ->name('datenschutz.upload-bedingungen-pdf');
    Route::post('/ds/hinweis-ok', [DatenschutzController::class, 'hinweisOk'])
        ->name('datenschutz.hinweis-ok');
});
