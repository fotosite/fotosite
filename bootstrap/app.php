<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/system.php'));
            Route::middleware(['web', \App\Http\Middleware\MandantActiveCheck::class])->group(base_path('routes/mandant.php'));
            Route::middleware('web')->group(base_path('routes/customer.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global — applied to every response regardless of route group
        $middleware->append(\App\Http\Middleware\NoIndexHeader::class);

        // Web group — require an active session
        $middleware->web(append: [
            \App\Http\Middleware\SessionHijackProtection::class,
            \App\Http\Middleware\AnonymousSessionTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
