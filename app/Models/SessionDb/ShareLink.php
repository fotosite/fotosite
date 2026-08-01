<?php

namespace App\Models\SessionDb;

class ShareLink extends SessionDbModel
{
    protected $table = 'share_link';
    protected $primaryKey = 'sl_id';
    public $timestamps = false;

    protected $fillable = [
        'code',
        'mand_id',
        'sec_level',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
