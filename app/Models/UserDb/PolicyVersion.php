<?php
/**
 * FILE:        app/Models/UserDb/PolicyVersion.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   get(string $key): string — Liest pv_value für $key; cached pro
 *                  Request in einer statischen Variable (kein erneuter DB-Hit
 *                  bei mehrfachem Aufruf desselben Keys innerhalb einer Request).
 *
 * CALLS:       (none)
 *
 * DB ACCESS:   userdb.policy_versions.pv_key, pv_value
 */

namespace App\Models\UserDb;

class PolicyVersion extends UserDbModel
{
    protected $table = 'policy_versions';
    protected $primaryKey = 'pv_key';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'pv_key',
        'pv_value',
    ];

    private static array $cache = [];

    public static function get(string $key): string
    {
        if (! array_key_exists($key, self::$cache)) {
            self::$cache[$key] = static::find($key)?->pv_value ?? '';
        }

        return self::$cache[$key];
    }
}
