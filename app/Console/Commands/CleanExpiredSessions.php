<?php
/**
 * FILE:        app/Console/Commands/CleanExpiredSessions.php
 * VERSION:     1.0.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-06-03
 *
 * ZWECK:       Löscht abgelaufene Sessions aus sessiondb.session.
 *              Wird per Scheduler oder manuell via php artisan sessions:clean aufgerufen.
 *
 * FUNCTIONS:   handle() — Löscht alle Zeilen in sessiondb.session wo expires_at < now()
 *                          Writes: sessiondb.session (DELETE)
 *
 * CALLS:       Illuminate\Support\Facades\DB::connection('sessiondb')->table('session')
 *
 * DB ACCESS:   sessiondb.session.expires_at
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanExpiredSessions extends Command
{
    protected $signature = 'sessions:clean';

    protected $description = 'Löscht abgelaufene Sessions aus sessiondb';

    public function handle(): void
    {
        DB::connection('sessiondb')
            ->table('session')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info('Abgelaufene Sessions bereinigt.');
    }
}
