<?php
/**
 * FILE:        app/Models/UserDb/Passkey.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   — (Data-Shape-only Model; keine eigenen Methoden)
 *
 * CALLS:       —
 *
 * DB ACCESS:   userdb.passkey.pk_id, user_type, user_id, credential_id,
 *              public_key, aaguid, sign_count, device_name, created_at, last_used_at
 */

namespace App\Models\UserDb;

class Passkey extends UserDbModel
{
    protected $table = 'passkey';
    protected $primaryKey = 'pk_id';
    public $timestamps = false;

    protected $fillable = [
        'pk_id',
        'user_type',
        'user_id',
        'credential_id',
        'public_key',
        'aaguid',
        'sign_count',
        'device_name',
        'created_at',
        'last_used_at',
    ];

    protected $casts = [
        'sign_count'   => 'integer',
        'created_at'   => 'datetime',
        'last_used_at' => 'datetime',
    ];
}
