<?php
/**
 * FILE:        app/Models/UserDb/Invite.php
 * VERSION:     1.1.0
 *
 * FUNCTIONS:   scopeValid($query) — filters records where expires_at > now()
 *
 * CALLS:       (none)
 *
 * DB ACCESS:   userdb.invite.inv_id, inv_email, inv_token_hash, inv_type,
 *              inv_user_type, inv_user_id, inv_mand_id, created_at, expires_at,
 *              is_primary
 *
 * CHANGES:     1.1.0 (2026-06-22) is_primary ergänzt ($fillable, $casts) — trägt
 *              die is_primary-Absicht einer syst-Einladung bis zur Registrierung.
 */

namespace App\Models\UserDb;

class Invite extends UserDbModel
{
    protected $table      = 'invite';
    protected $primaryKey = 'inv_id';
    public    $timestamps = false;

    protected $fillable = [
        'inv_email',
        'inv_token_hash',
        'inv_type',
        'inv_user_type',
        'inv_user_id',
        'inv_mand_id',
        'created_at',
        'expires_at',
        'is_primary',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_primary' => 'boolean',
    ];

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
