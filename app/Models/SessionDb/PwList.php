<?php

namespace App\Models\SessionDb;

class PwList extends SessionDbModel
{
    protected $table = 'pw_list';
    protected $primaryKey = 'pwlist_id';
    public $timestamps = false;

    protected $fillable = [
        'mand_id',
        'pw1',
        'pw2',
        'pw3',
        'pw4',
        'pw5',
        'pw6',
        'valid_from',
        'valid_until',
    ];

    protected $hidden = ['pw1', 'pw2', 'pw3', 'pw4', 'pw5', 'pw6'];

    protected $casts = [
        'valid_from'  => 'datetime',
        'valid_until' => 'datetime',
    ];
}
