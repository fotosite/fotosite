<?php
/**
 * FILE:        app/Extensions/SessionDbSessionHandler.php
 * VERSION:     2.1.0
 *
 * FUNCTIONS:   open(savePath, sessionName) — SessionHandlerInterface-Stub; immer true.
 *              close()                     — SessionHandlerInterface-Stub; immer true.
 *              read(id)                    — Liest payload anhand sess_id; prüft
 *                                           Ablauf via last_activity (Datetime-Vergleich).
 *                                           Reads: sessiondb.session.sess_id,
 *                                                  payload, last_activity
 *              write(id, data)             — Aktualisiert payload + Metadaten (UPDATE)
 *                                           oder legt neue Session an (INSERT).
 *                                           sess_token = Session-Key ($id, max 128 Zeichen).
 *                                           payload    = vollständiger Session-Inhalt ($data).
 *                                           INSERT-Fehler werden geloggt (kein stilles Scheitern).
 *                                           Race-Condition-Fallback auf UPDATE.
 *                                           Writes: sessiondb.session.*
 *              destroy(id)                 — Löscht Session anhand sess_id.
 *                                           Writes: sessiondb.session.sess_id
 *              gc(max_lifetime)            — Löscht abgelaufene Sessions per
 *                                           last_activity-Vergleich (Datetime).
 *                                           Writes: sessiondb.session.last_activity
 *              db()                        — Gibt Query-Builder für session-Tabelle
 *                                           zurück (useWritePdo).
 *
 * CALLS:       Illuminate\Database\Connection::table()
 *              Illuminate\Database\QueryException
 *              Illuminate\Support\Carbon::now()
 *              Illuminate\Support\Carbon::parse()
 *              Illuminate\Support\Facades\Log::error()
 *
 * DB ACCESS:   sessiondb.session.sess_id, sess_token, payload, user_type,
 *              last_activity, expires_at, ip_hash, ua_hash, created_at
 */

namespace App\Extensions;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use SessionHandlerInterface;

class SessionDbSessionHandler implements SessionHandlerInterface
{
    private ?Request $request = null;

    public function __construct(
        private readonly Connection  $connection,
        private readonly string      $table,
        private readonly int         $minutes,
        private readonly ?Container  $container = null,
    ) {
        if ($container?->bound('request')) {
            $this->request = $container->make('request');
        }
    }

    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $session = $this->db()->where('sess_id', $id)->first();

        if (! $session) {
            return '';
        }

        if (Carbon::parse($session->last_activity)->lt(Carbon::now()->subMinutes($this->minutes))) {
            return '';
        }

        return $session->payload ?? '';
    }

    public function write(string $id, string $data): bool
    {
        $now = Carbon::now();

        $updatePayload = [
            'payload'       => $data,
            'last_activity' => $now->toDateTimeString(),
            'expires_at'    => $now->copy()->addMinutes($this->minutes)->toDateTimeString(),
            'ip_hash'       => hash('sha256', $this->request?->ip() ?? ''),
            'ua_hash'       => hash('sha256', mb_substr(
                mb_convert_encoding(
                    (string) ($this->request?->header('User-Agent') ?? ''), 'UTF-8'
                ), 0, 500
            )),
        ];

        $exists = $this->db()->where('sess_id', $id)->exists();

        if ($exists) {
            $this->db()->where('sess_id', $id)->update($updatePayload);
        } else {
            try {
                $this->db()->insert(array_merge($updatePayload, [
                    'sess_id'    => $id,
                    'sess_token' => substr($id, 0, 128),
                    'user_type'  => 'anon',
                    'created_at' => $now->toDateTimeString(),
                ]));
            } catch (QueryException $e) {
                Log::error('SessionDbSessionHandler: INSERT fehlgeschlagen', [
                    'session_id' => $id,
                    'sqlstate'   => $e->errorInfo[0] ?? null,
                    'error'      => $e->getMessage(),
                ]);
                // Race-Condition-Fallback: paralleler Request hat bereits eingefügt
                $this->db()->where('sess_id', $id)->update($updatePayload);
            }
        }

        return true;
    }

    public function destroy(string $id): bool
    {
        $this->db()->where('sess_id', $id)->delete();

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return $this->db()
            ->where('last_activity', '<=', Carbon::now()->subSeconds($max_lifetime)->toDateTimeString())
            ->delete();
    }

    private function db(): Builder
    {
        return $this->connection->table($this->table)->useWritePdo();
    }
}
