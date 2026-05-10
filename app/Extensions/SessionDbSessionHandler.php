<?php
/**
 * FILE:        app/Extensions/SessionDbSessionHandler.php
 * VERSION:     1.1.0
 *
 * FUNCTIONS:   read(sessionId)              — Liest Session anhand sess_id; gibt
 *                                             base64-dekodiertes sess_token zurück.
 *                                             Reads: sessiondb.session.sess_id,
 *                                             sess_token, last_activity
 *              expired(session)             — Prüft ob last_activity älter als
 *                                             $minutes ist.
 *              getDefaultPayload(data)      — Erstellt Insert-/Update-Payload:
 *                                             sess_token, user_type (Default 'anon'),
 *                                             last_activity, expires_at, ip_hash,
 *                                             ua_hash.
 *              performInsert(sessionId, payload) — Fügt neuen Session-Datensatz ein;
 *                                             fällt bei Duplikat auf Update zurück.
 *                                             Writes: sessiondb.session.*
 *              performUpdate(sessionId, payload) — Aktualisiert bestehenden Datensatz.
 *                                             Writes: sessiondb.session.*
 *              destroy(sessionId)           — Löscht Session-Datensatz.
 *                                             Writes: sessiondb.session.sess_id
 *              gc(lifetime)                 — Löscht abgelaufene Sessions.
 *                                             Writes: sessiondb.session.last_activity
 *
 * CALLS:       Illuminate\Database\QueryException
 *              Illuminate\Support\Arr::set()
 *              Illuminate\Support\Carbon::now()
 *              Illuminate\Support\Carbon::parse()
 *
 * DB ACCESS:   sessiondb.session.sess_id, sess_token, user_type, last_activity,
 *              expires_at, ip_hash, ua_hash, created_at
 */

namespace App\Extensions;

use Illuminate\Database\QueryException;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class SessionDbSessionHandler extends DatabaseSessionHandler
{
    protected string $sessionIdColumn = 'sess_id';

    public function read($sessionId): string|false
    {
        $session = (object) $this->getQuery()
            ->where($this->sessionIdColumn, $sessionId)
            ->first();

        if ($this->expired($session)) {
            $this->exists = true;

            return '';
        }

        if (isset($session->sess_token)) {
            $this->exists = true;

            return base64_decode($session->sess_token);
        }

        return '';
    }

    protected function expired($session): bool
    {
        return isset($session->last_activity) &&
            Carbon::parse($session->last_activity)->lt(Carbon::now()->subMinutes($this->minutes));
    }

    protected function getDefaultPayload($data): array
    {
        $payload = [
            'sess_token'    => base64_encode($data),
            'user_type'     => 'anon',
            'last_activity' => Carbon::now()->toDateTimeString(),
            'expires_at'    => Carbon::now()->addMinutes($this->minutes)->toDateTimeString(),
        ];

        if ($this->container && $this->container->bound('request')) {
            $request = $this->container->make('request');
            $payload['ip_hash'] = hash('sha256', $request->ip() ?? '');
            $payload['ua_hash'] = hash('sha256', mb_substr(
                mb_convert_encoding((string) $request->header('User-Agent'), 'UTF-8'), 0, 500
            ));
        }

        return $payload;
    }

    protected function performInsert($sessionId, $payload): bool|null
    {
        $payload['created_at'] = Carbon::now()->toDateTimeString();

        try {
            return $this->getQuery()->insert(Arr::set($payload, $this->sessionIdColumn, $sessionId));
        } catch (QueryException) {
            $this->performUpdate($sessionId, $payload);
        }

        return null;
    }

    protected function performUpdate($sessionId, $payload): int
    {
        return $this->getQuery()->where($this->sessionIdColumn, $sessionId)->update($payload);
    }

    public function destroy($sessionId): bool
    {
        $this->getQuery()->where($this->sessionIdColumn, $sessionId)->delete();

        return true;
    }

    public function gc($lifetime): int
    {
        return $this->getQuery()
            ->where('last_activity', '<=', Carbon::now()->subSeconds($lifetime)->toDateTimeString())
            ->delete();
    }
}
