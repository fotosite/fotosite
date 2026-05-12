<?php
/**
 * FILE:        routes/system.php
 * VERSION:     1.4.0
 *
 * DESCRIPTION:
 *   Routes for the /system area (role: syst).
 *   Authenticated routes require the syst.auth middleware.
 *   Token routes (register, password-reset) are public (web only).
 *
 * PREFIX:      /system
 * MIDDLEWARE:  web, syst.auth  (authenticated group)
 *              web             (public token group)
 * NAME-PREFIX: system.
 *
 * ROUTES (authenticated):
 *   GET    /system/dashboard                     → SystemDashboardController@index
 *   GET    /system/profile                       → SystemProfileController@edit
 *   PATCH  /system/profile                       → SystemProfileController@updateProfile
 *   PATCH  /system/profile/password              → SystemProfileController@updatePassword
 *   GET    /system/users                         → SystemUserController@index
 *   POST   /system/users/invite                  → SystemUserController@invite
 *   POST   /system/users/{id}/password-reset     → SystemUserController@sendPasswordReset
 *   DELETE /system/users/{id}                    → SystemUserController@destroy
 *   GET    /system/mandanten                     → SystemMandantController@index
 *   POST   /system/mandanten/invite              → SystemMandantController@invite
 *   GET    /system/mandanten/{id}                → SystemMandantController@show
 *   GET    /system/mandanten/{id}/edit           → SystemMandantController@edit
 *   PATCH  /system/mandanten/{id}                → SystemMandantController@update
 *   DELETE /system/mandanten/{id}                → SystemMandantController@destroy
 *
 * ROUTES (public — token):
 *   GET    /system/register/{token}              → SystemUserController@showRegister
 *   POST   /system/register/{token}              → SystemUserController@handleRegister
 *   GET    /system/password-reset/{token}        → SystemUserController@showPasswordReset
 *   POST   /system/password-reset/{token}        → SystemUserController@handlePasswordReset
 *   GET    /system/mand-register/{token}         → SystemMandantController@showRegister
 *   POST   /system/mand-register/{token}         → SystemMandantController@handleRegister
 */

use App\Http\Controllers\UserDb\SystemDashboardController;
use App\Http\Controllers\UserDb\SystemMandantController;
use App\Http\Controllers\UserDb\SystemProfileController;
use App\Http\Controllers\UserDb\SystemUserController;
use Illuminate\Support\Facades\Route;

// ── Authenticated area ────────────────────────────────────────
Route::middleware(['web', 'syst.auth'])
    ->prefix('system')
    ->name('system.')
    ->group(function () {
        Route::get('/dashboard', [SystemDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/profile', [SystemProfileController::class, 'edit'])
            ->name('profile');
        Route::patch('/profile', [SystemProfileController::class, 'updateProfile'])
            ->name('profile.update');
        Route::patch('/profile/password', [SystemProfileController::class, 'updatePassword'])
            ->name('profile.password');

        Route::get('/users', [SystemUserController::class, 'index'])
            ->name('users.index');
        Route::post('/users/invite', [SystemUserController::class, 'invite'])
            ->name('users.invite');
        Route::post('/users/{id}/password-reset', [SystemUserController::class, 'sendPasswordReset'])
            ->name('users.password-reset');
        Route::delete('/users/{id}', [SystemUserController::class, 'destroy'])
            ->name('users.destroy');

        Route::get('/mandanten', [SystemMandantController::class, 'index'])
            ->name('mandanten.index');
        Route::post('/mandanten/invite', [SystemMandantController::class, 'invite'])
            ->name('mandanten.invite');
        Route::get('/mandanten/{id}', [SystemMandantController::class, 'show'])
            ->name('mandanten.show');
        Route::get('/mandanten/{id}/edit', [SystemMandantController::class, 'edit'])
            ->name('mandanten.edit');
        Route::patch('/mandanten/{id}', [SystemMandantController::class, 'update'])
            ->name('mandanten.update');
        Route::delete('/mandanten/{id}', [SystemMandantController::class, 'destroy'])
            ->name('mandanten.destroy');
    });

// ── Public token routes (no syst.auth) ───────────────────────
Route::middleware(['web'])
    ->prefix('system')
    ->name('system.')
    ->group(function () {
        Route::get('/register/{token}', [SystemUserController::class, 'showRegister'])
            ->name('register');
        Route::post('/register/{token}', [SystemUserController::class, 'handleRegister'])
            ->name('register.handle');
        Route::get('/password-reset/{token}', [SystemUserController::class, 'showPasswordReset'])
            ->name('password.reset');
        Route::post('/password-reset/{token}', [SystemUserController::class, 'handlePasswordReset'])
            ->name('password.reset.handle');

        Route::get('/mand-register/{token}', [SystemMandantController::class, 'showRegister'])
            ->name('mand.register');
        Route::post('/mand-register/{token}', [SystemMandantController::class, 'handleRegister'])
            ->name('mand.register.handle');
    });
