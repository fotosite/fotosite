<?php
/**
 * FILE:        app/Providers/PasskeyServiceProvider.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   register() — Bindet PasskeyRepository, PasskeyUserEntityRepository
 *                           und PasskeySessionStorage als Singletons in den Container
 *
 * CALLS:       App\Models\UserDb\Passkey::__construct()
 *              App\Services\Passkey\PasskeyRepository::__construct()
 *              App\Services\Passkey\PasskeyUserEntityRepository::__construct()
 *              App\Services\Passkey\PasskeySessionStorage::__construct()
 *
 * DB ACCESS:   — (nur Registrierung; DB-Zugriff erfolgt in den Services)
 */

namespace App\Providers;

use App\Models\UserDb\Passkey;
use App\Services\Passkey\PasskeyRepository;
use App\Services\Passkey\PasskeySessionStorage;
use App\Services\Passkey\PasskeyUserEntityRepository;
use Illuminate\Support\ServiceProvider;

class PasskeyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            PasskeyRepository::class,
            fn () => new PasskeyRepository(new Passkey())
        );

        $this->app->singleton(
            PasskeyUserEntityRepository::class,
            fn () => new PasskeyUserEntityRepository()
        );

        $this->app->singleton(
            PasskeySessionStorage::class,
            fn () => new PasskeySessionStorage()
        );
    }
}
