<?php
/**
 * FILE:        app/Models/UserDb/SystUser.php
 * VERSION:     1.1.0
 * DATE:        2026-06-22
 *
 * CHANGES:     1.1.0 (2026-06-22) is_primary ergänzt ($fillable, $casts) —
 *              markiert den primären System-User mit erweiterten Rechten
 *              (Einladen weiterer Primär-User, Löschschutz für Primär-User).
 */

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
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected $hidden = ['syst_pw_hash'];
}
