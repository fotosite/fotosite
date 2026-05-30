<?php
/**
 * FILE:        app/Models/SessionDb/CustInvite.php
 * VERSION:     1.1.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-05-30
 * PURPOSE:     Cust-Einladungs-Token — ein Datensatz pro ausgestellter Einladung
 *
 * FUNCTIONS:   (none — data model only)
 *
 * CALLS:       (none)
 *
 * DB ACCESS:   sessiondb.cust_invite.invite_id, mand_id, cust_email, cust_alias,
 *              sec_level, token, created_at, expires_at, used
 */

namespace App\Models\SessionDb;

class CustInvite extends SessionDbModel
{
    protected $table = 'cust_invite';
    protected $primaryKey = 'invite_id';
    public $timestamps = false;

    protected $fillable = [
        'invite_id',
        'mand_id',
        'cust_email',
        'cust_alias',
        'sec_level',
        'token',
        'created_at',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'used'       => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
