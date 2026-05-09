<?php

namespace App\Models\UserDb;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CustUser extends UserDbModel
{
    protected $table = 'cust_user';
    protected $primaryKey = 'cust_id';
    public $timestamps = false;

    protected $fillable = [
        'cust_uname',
        'cust_email',
        'cust_tel',
        'cust_firstname',
        'cust_lastname',
        'cust_street+nr',
        'cust_postcode_city',
        'cust_company',
        'cust_pw_hash',
    ];

    protected $hidden = ['cust_pw_hash'];

    public function passcodes(): HasMany
    {
        return $this->hasMany(CustPcode::class, 'cust_id', 'cust_id');
    }
}
