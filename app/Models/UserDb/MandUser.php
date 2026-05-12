<?php
/**
 * FILE:        app/Models/UserDb/MandUser.php
 * VERSION:     1.2.0
 *
 * FUNCTIONS:   passcodes()     — hasMany CustPcode via mand_id
 *
 * CALLS:       App\Models\UserDb\CustPcode
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_uname, mand_email, mand_tel,
 *              mand_firstname, mand_lastname, mand_street+nr,
 *              mand_postcode+city, mand_company, mand_pw_hash,
 *              mand_prefstat, mand_cust_2fa, active, valid_to
 */

namespace App\Models\UserDb;

use Illuminate\Database\Eloquent\Relations\HasMany;

class MandUser extends UserDbModel
{
    protected $table = 'mand_user';
    protected $primaryKey = 'mand_id';
    public $timestamps = false;

    protected $fillable = [
        'mand_uname',
        'mand_email',
        'mand_tel',
        'mand_firstname',
        'mand_lastname',
        'mand_street+nr',
        'mand_postcode+city',
        'mand_company',
        'mand_pw_hash',
        'mand_prefstat',
        'mand_cust_2fa',
        'active',
        'has_public_content',
        'valid_to',
    ];

    protected $casts = [
        'active'             => 'boolean',
        'mand_cust_2fa'      => 'boolean',
        'has_public_content' => 'boolean',
        'valid_to'           => 'date',
    ];

    protected $hidden = ['mand_pw_hash'];

    public function passcodes(): HasMany
    {
        return $this->hasMany(CustPcode::class, 'mand_id', 'mand_id');
    }
}
