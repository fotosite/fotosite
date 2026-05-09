<?php

namespace App\Models\UserDb;

class SystUser extends UserDbModel
{
    protected $table = 'syst_user';
    protected $primaryKey = 'syst_id';
    public $timestamps = false;

    protected $fillable = [
        'syst_uname',
        'syst_email',
        'syst_tel',
        'syst_firstname',
        'syst_lastname',
        'syst_street+nr',
        'syst_pcode+city',
        'syst_company',
        'syst_pw_hash',
    ];

    protected $hidden = ['syst_pw_hash'];
}
