<?php
/**
 * FILE:        routes/system.php
 * VERSION:     1.2.0
 *
 * DESCRIPTION:
 *   Routes for the /system area (role: syst).
 *   All routes require the syst.auth middleware.
 *
 * PREFIX:      /system
 * MIDDLEWARE:  web, syst.auth
 * NAME-PREFIX: system.
 *
 * ROUTES:
 *   GET   /system/dashboard          → SystemDashboardController@index        → system.dashboard
 *   GET   /system/profile            → SystemProfileController@edit           → system.profile
 *   PATCH /system/profile            → SystemProfileController@updateProfile  → system.profile.update
 *   PATCH /system/profile/password   → SystemProfileController@updatePassword → system.profile.password
 */

use App\Http\Controllers\UserDb\SystemDashboardController;
use App\Http\Controllers\UserDb\SystemProfileController;
use Illuminate\Support\Facades\Route;

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
    });
