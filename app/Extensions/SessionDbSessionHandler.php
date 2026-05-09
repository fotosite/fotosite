<?php

namespace App\Extensions;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Arr;

class SessionDbSessionHandler extends DatabaseSessionHandler
{
    /**
     * The session ID column used in the session table instead of Laravel's default 'id'.
     */
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

        if (isset($session->payload)) {
            $this->exists = true;

            return base64_decode($session->payload);
        }

        return '';
    }

    protected function performInsert($sessionId, $payload)
    {
        try {
            return $this->getQuery()->insert(Arr::set($payload, $this->sessionIdColumn, $sessionId));
        } catch (\Illuminate\Database\QueryException) {
            $this->performUpdate($sessionId, $payload);
        }
    }

    protected function performUpdate($sessionId, $payload)
    {
        return $this->getQuery()->where($this->sessionIdColumn, $sessionId)->update($payload);
    }

    public function destroy($sessionId): bool
    {
        $this->getQuery()->where($this->sessionIdColumn, $sessionId)->delete();

        return true;
    }
}
