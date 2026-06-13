<?php
/**
 * FILE:        app/Providers/AppServiceProvider.php
 * VERSION:     1.2.0
 *
 * FUNCTIONS:   register() — Leer; keine eigenen Bindings.
 *              boot()     — Registriert den Custom-Session-Driver 'sessiondb'.
 *                           Definiert benannte RateLimiter für alle throttle-Routen;
 *                           bei DEBUGMODE=true werden alle Limits deaktiviert.
 *
 * CALLS:       Illuminate\Support\Facades\Session::extend()
 *              App\Extensions\SessionDbSessionHandler::__construct()
 *              Illuminate\Support\Facades\RateLimiter::for()
 *
 * DB ACCESS:   none (Registrierung; DB-Zugriff erfolgt im SessionHandler)
 */

namespace App\Providers;

use App\Extensions\SessionDbSessionHandler;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Session::extend('sessiondb', function (Application $app) {
            $config = $app['config']['session'];

            return new SessionDbSessionHandler(
                $app['db']->connection($config['connection']),
                $config['table'],
                $config['lifetime'],
                $app,
            );
        });

        $this->registerRateLimiters();
    }

    private function registerRateLimiters(): void
    {
        $debug = config('app.debugmode');

        // routes/auth.php: verify-email GET + verification-notification POST
        RateLimiter::for('email-verify', function (Request $request) use ($debug) {
            if ($debug) return Limit::none();
            return Limit::perMinute(6)->by($request->ip());
        });

        // routes/customer.php: POST /customer/login
        RateLimiter::for('cust-login', function (Request $request) use ($debug) {
            if ($debug) return Limit::none();
            return Limit::perMinute(10)->by($request->ip());
        });

        // routes/customer.php: POST /customer/login/anon
        RateLimiter::for('cust-anon-login', function (Request $request) use ($debug) {
            if ($debug) return Limit::none();
            return Limit::perMinutes(1, 5)->by($request->ip());
        });

        // routes/customer.php: POST /customer/login/2fa
        // routes/customer.php: POST /customer/password-reset
        // routes/mandant.php:  POST /mandant/password-reset
        RateLimiter::for('login-2fa', function (Request $request) use ($debug) {
            if ($debug) return Limit::none();
            return Limit::perMinutes(10, 3)->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) use ($debug) {
            if ($debug) return Limit::none();
            return Limit::perMinutes(10, 3)->by($request->ip());
        });
    }
}
