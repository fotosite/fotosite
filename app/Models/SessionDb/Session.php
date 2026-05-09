<?php

namespace App\Models\SessionDb;

class Session extends SessionDbModel
{
    protected $table = 'session';
    protected $primaryKey = 'sess_id';
    public $timestamps = false;

    protected $fillable = [
        'sess_token',
        'user_type',
        'syst_id',
        'mand_id',
        'cust_id',
        'cust_passcode',
        'ip_hash',
        'ua_hash',
        'created_at',
        'last_activity',
        'expires_at',
    ];

    protected $casts = [
        'created_at'    => 'datetime',
        'last_activity' => 'datetime',
        'expires_at'    => 'datetime',
    ];
}
