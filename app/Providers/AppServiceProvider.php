<?php
/**
 * FILE:        app/Providers/AppServiceProvider.php
 * VERSION:     1.1.0
 *
 * FUNCTIONS:   register() — Leer; keine eigenen Bindings.
 *              boot()     — Registriert den Custom-Session-Driver 'sessiondb'.
 *                           Der Driver-Name muss mit SESSION_DRIVER in .env
 *                           übereinstimmen. Verwendet DB-Connection 'sessiondb',
 *                           Tabelle und Lifetime aus config/session.php.
 *
 * CALLS:       Illuminate\Support\Facades\Session::extend()
 *              App\Extensions\SessionDbSessionHandler::__construct()
 *
 * DB ACCESS:   none (Registrierung; DB-Zugriff erfolgt im SessionHandler)
 */

namespace App\Providers;

use App\Extensions\SessionDbSessionHandler;
use Illuminate\Contracts\Foundation\Application;
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
    }
}
