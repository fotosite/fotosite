<?php
/**
 * FILE:        routes/system.php
 * VERSION:     1.1.0
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
 *   GET /system/dashboard → SystemDashboardController@index → system.dashboard
 */

use App\Http\Controllers\UserDb\SystemDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'syst.auth'])
    ->prefix('system')
    ->name('system.')
    ->group(function () {
        Route::get('/dashboard', [SystemDashboardController::class, 'index'])
            ->name('dashboard');
    });
