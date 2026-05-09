<?php

namespace App\Models\FotoBlobDb;

class FotoObjDb extends FotoBlobDbModel
{
    protected $table = 'foto_obj_db';
    protected $primaryKey = 'fod_id';
    public $timestamps = false;

    protected $fillable = [
        'fo_id',
        'fod_obj',
    ];
}
