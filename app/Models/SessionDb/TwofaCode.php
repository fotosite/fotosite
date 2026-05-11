<?php
/**
 * FILE:        app/Models/SessionDb/TwofaCode.php
 * VERSION:     1.1.0
 *
 * FUNCTIONS:   (none — data model only)
 *
 * CALLS:       (none)
 *
 * DB ACCESS:   sessiondb.twofa_code.tfa_id, user_type, user_id, tfa_purpose,
 *              tfa_code_hash, tfa_expires_at, tfa_used, created_at
 */

namespace App\Models\SessionDb;

class TwofaCode extends SessionDbModel
{
    protected $table = 'twofa_code';
    protected $primaryKey = 'tfa_id';
    public $timestamps = false;

    protected $fillable = [
        'user_type',
        'user_id',
        'tfa_purpose',
        'tfa_code_hash',
        'tfa_expires_at',
        'tfa_used',
        'created_at',
    ];

    protected $casts = [
        'tfa_expires_at' => 'datetime',
        'tfa_used'       => 'boolean',
        'created_at'     => 'datetime',
    ];

    protected $hidden = ['tfa_code_hash'];
}
