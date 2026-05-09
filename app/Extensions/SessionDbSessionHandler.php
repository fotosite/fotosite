<?php

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
