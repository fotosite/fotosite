<?php
/**
 * FILE:        app/Extensions/SessionDbSessionHandler.php
 * VERSION:     2.3.0
 *
 * FUNCTIONS:   open(savePath, sessionName) — SessionHandlerInterface-Stub; immer true.
 *              close()                     — SessionHandlerInterface-Stub; immer true.
 *              read(id)                    — Liest payload via sess_token + expires_at-Prüfung
 *                                           (DB-seitig, ein Query). Gibt '' zurück wenn
 *                                           keine gültige Session gefunden.
 *                                           Reads: sessiondb.session.sess_token,
 *                                                  expires_at, payload
 *              write(id, data)             — Aktualisiert payload + Metadaten (UPDATE via
 *                                           sess_token) oder legt neue Session an (INSERT).
 *                                           sess_token = Session-Key ($id, max 128 Zeichen).
 *                                           payload    = vollständiger Session-Inhalt ($data).
 *                                           INSERT-Fehler werden geloggt (kein stilles Scheitern).
 *                                           Race-Condition-Fallback auf UPDATE via sess_token.
 *                                           Writes: sessiondb.session.*
 *              destroy(id)                 — Löscht Session anhand sess_token.
 *                                           Writes: sessiondb.session.sess_token
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
 * DB ACCESS:   sessiondb.session.sess_token, payload, user_type, cust_passcode,
 *              ip_hash, ua_hash, created_at, last_activity, expires_at
 *              (sess_id: BIGINT AUTO_INCREMENT — wird nicht im INSERT gesetzt)
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
        return $this->db()
            ->where('sess_token', $id)
            ->where('expires_at', '>', Carbon::now()->toDateTimeString())
            ->value('payload') ?? '';
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

        $exists = $this->db()->where('sess_token', $id)->exists();

        if ($exists) {
            $this->db()->where('sess_token', $id)->update($updatePayload);
        } else {
            try {
                $this->db()->insert(array_merge($updatePayload, [
                    'sess_token'    => substr($id, 0, 128),
                    'user_type'     => 'anon',
                    'cust_passcode' => 0,
                    'created_at'    => $now->toDateTimeString(),
                ]));
            } catch (QueryException $e) {
                Log::error('SessionDbSessionHandler: INSERT fehlgeschlagen', [
                    'session_id' => $id,
                    'sqlstate'   => $e->errorInfo[0] ?? null,
                    'error'      => $e->getMessage(),
                ]);
                // Race-Condition-Fallback: paralleler Request hat bereits eingefügt
                $this->db()->where('sess_token', $id)->update($updatePayload);
            }
        }

        return true;
    }

    public function destroy(string $id): bool
    {
        $this->db()->where('sess_token', $id)->delete();

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
