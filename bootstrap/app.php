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
        // Reihenfolge CheckPolicyVersion vor CheckPflichtfelder vor CheckWelcome
        // ist bewusst: eine veraltete Datenschutz-/Upload-Policy ist eine
        // rechtliche Pflichtbestätigung und hat Vorrang vor fehlenden
        // Pflichtangaben (Telefon/Strasse/PLZOrt/Firma), fehlende Pflichtangaben
        // wiederum haben Vorrang vor der reinen Onboarding-Willkommensseite
        // (siehe CheckWelcome-Docblock und CheckPflichtfelder-Docblock).
        $middleware->web(
            prepend: [
                \App\Http\Middleware\BlockSamsungBrowser::class,
            ],
            append: [
                \App\Http\Middleware\SessionHijackProtection::class,
                \App\Http\Middleware\SessionIdleTimeout::class,
                \App\Http\Middleware\ValidateUserExists::class,
                \App\Http\Middleware\AutoLoginTrustedDevice::class,
                \App\Http\Middleware\CheckPolicyVersion::class,
                \App\Http\Middleware\CheckPflichtfelder::class,
                \App\Http\Middleware\CheckWelcome::class,
            ],
        );

        // Named middleware aliases
        $middleware->alias([
            'syst.auth' => \App\Http\Middleware\SystUserCheck::class,
            'role'      => \App\Http\Middleware\RequireRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withProviders([
        App\Providers\PasskeyServiceProvider::class,
    ])
    ->create();
