<?php
/**
 * FILE:        app/Models/UserDb/CustUser.php
 * VERSION:     1.3.0
 *
 * FUNCTIONS:   passcodes()     — hasMany CustPcode via cust_id
 *
 * CALLS:       App\Models\UserDb\CustPcode
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_uname, cust_email, cust_tel,
 *              cust_firstname, cust_lastname, cust_street+nr,
 *              cust_postcode_city, cust_company, cust_pw_hash, cust_2fa_opt_in,
 *              ds_accepted_at, ds_version, upload_terms_accepted_at,
 *              upload_terms_version
 *
 * CHANGES:     1.3.0 (2026-06-18) upload_terms_accepted_at, upload_terms_version
 *              ergänzt (DDL um diese Spalten erweitert — Policy-Versions-Popup
 *              speichert Upload-Bedingungen-Zustimmung jetzt dauerhaft in der DB
 *              statt nur per Session-Flag)
 *              1.2.0 (2026-06-16) ds_accepted_at, ds_version ergänzt (Datenschutz-Feature)
 */

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
        'cust_2fa_opt_in',
        'ds_accepted_at',
        'ds_version',
        'upload_terms_accepted_at',
        'upload_terms_version',
    ];

    protected $casts = [
        'cust_2fa_opt_in'          => 'boolean',
        'ds_accepted_at'           => 'datetime',
        'upload_terms_accepted_at' => 'datetime',
    ];

    protected $hidden = ['cust_pw_hash'];

    public function passcodes(): HasMany
    {
        return $this->hasMany(CustPcode::class, 'cust_id', 'cust_id');
    }
}
