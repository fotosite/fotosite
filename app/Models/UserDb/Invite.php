<?php
/**
 * FILE:        app/Models/UserDb/Invite.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   scopeValid($query) — filters records where expires_at > now()
 *
 * CALLS:       (none)
 *
 * DB ACCESS:   userdb.invite.inv_id, inv_email, inv_token_hash, inv_type,
 *              inv_user_type, inv_user_id, inv_mand_id, created_at, expires_at
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
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
