<?php
/**
 * FILE:        app/Models/UserDb/CustPcode.php
 * VERSION:     1.1.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-05-30
 * PURPOSE:     Mandant-Kunden-Zuordnung mit Sicherheitsstufe und internem Alias
 *
 * FUNCTIONS:   mandUser() — belongsTo MandUser via mand_id
 *              custUser() — belongsTo CustUser via cust_id
 *
 * CALLS:       App\Models\UserDb\MandUser
 *              App\Models\UserDb\CustUser
 *
 * DB ACCESS:   userdb.cust_pcode.pcode_id, mand_id, cust_id, cust_passcode,
 *              pcode_prefstat, cust_alias
 */

namespace App\Models\UserDb;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustPcode extends UserDbModel
{
    protected $table = 'cust_pcode';
    protected $primaryKey = 'pcode_id';
    public $timestamps = false;

    protected $fillable = [
        'mand_id',
        'cust_id',
        'cust_passcode',
        'pcode_prefstat',
        'cust_alias',
    ];

    public function mandUser(): BelongsTo
    {
        return $this->belongsTo(MandUser::class, 'mand_id', 'mand_id');
    }

    public function custUser(): BelongsTo
    {
        return $this->belongsTo(CustUser::class, 'cust_id', 'cust_id');
    }
}
