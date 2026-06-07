<?php
/**
 * FILE:        app/Models/UserDb/PasskeyDismissed.php
 * VERSION:     1.0.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-06-07
 * PURPOSE:     Model für passkey_dismissed-Tabelle (userdb)
 *              Speichert gerätespezifische "Nie wieder fragen"-Einträge
 *              für die Passkey-Registrierungsaufforderung.
 *
 * FUNCTIONS:   —
 * CALLS:       UserDbModel
 * DB ACCESS:   userdb.passkey_dismissed (pd_id, user_type, user_id, os, ua_hash, created_at)
 */

namespace App\Models\UserDb;

class PasskeyDismissed extends UserDbModel
{
    protected $table      = 'passkey_dismissed';
    protected $primaryKey = 'pd_id';
    public    $timestamps = false;

    protected $fillable = [
        'user_type',
        'user_id',
        'os',
        'ua_hash',
        'created_at',
    ];
}
