<?php
/**
 * FILE:        app/Models/SessionDb/TrustedDevice.php
 * VERSION:     1.0.0
 * DATE:        2026-07-10
 * PURPOSE:     Model für trusted_device-Tabelle (sessiondb)
 *              Speichert Geräte, die per "Dieses Gerät als sicher merken"
 *              beim Login von der 2FA-Abfrage ausgenommen wurden
 *              (Cookie-Token-Hash + UA-Hash-Bindung, 30 Tage gültig).
 *
 * FUNCTIONS:   —
 * CALLS:       SessionDbModel
 * DB ACCESS:   sessiondb.trusted_device (td_id, user_type, user_id, token_hash,
 *              ua_hash, device_label, last_used_at, expires_at, created_at)
 */

namespace App\Models\SessionDb;

class TrustedDevice extends SessionDbModel
{
    protected $table      = 'trusted_device';
    protected $primaryKey = 'td_id';
    public    $timestamps = false;

    protected $fillable = [
        'user_type',
        'user_id',
        'token_hash',
        'ua_hash',
        'device_label',
        'last_used_at',
        'expires_at',
        'created_at',
    ];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
        'created_at'   => 'datetime',
    ];
}
