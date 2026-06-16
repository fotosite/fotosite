<?php
/**
 * FILE:        app/Models/FotoDB/FotoObj.php
 * VERSION:     1.0.2
 *
 * FUNCTIONS:   agFoContexts()      — hasMany AgFoContext; reads fotodb.ag_fo_context.*
 *              asgFoContexts()     — hasMany AsgFoContext; reads fotodb.asg_fo_context.*
 *              mpFoContexts()      — hasMany MpFoContext; reads fotodb.mp_fo_context.*
 *              activityGroups()    — belongsToMany ActivityGroup via ag_fo_context
 *              activitySubgroups() — belongsToMany ActivitySubgroup via asg_fo_context
 *              mandProfiles()      — belongsToMany MandProfile via mp_fo_context
 *
 * CALLS:       App\Models\FotoDB\AgFoContext
 *              App\Models\FotoDB\AsgFoContext
 *              App\Models\FotoDB\MpFoContext
 *              App\Models\FotoDB\ActivityGroup
 *              App\Models\FotoDB\ActivitySubgroup
 *              App\Models\FotoDB\MandProfile
 *
 * DB ACCESS:   fotodb.foto_obj.fo_id, fo_is_video, fo_filename, fo_title,
 *              fo_subtitle, fo_text, mand_id, fo_sec_level, fo_datetime,
 *              db_saved, fo_filepath, fo_prefstat
 *              fotodb.ag_fo_context.ag_is_banner (pivot)
 *              fotodb.asg_fo_context.ags_is_banner (pivot)
 *
 * CHANGES:     1.0.1 (2026-06-14) ag_banner aus withPivot entfernt — DDL-Feld gelöscht
 *              1.0.2 (2026-06-16) fo_sec_code → fo_sec_level (TINYINT UNSIGNED)
 */

namespace App\Models\FotoDB;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FotoObj extends FotoDbModel
{
    protected $table = 'foto_obj';
    protected $primaryKey = 'fo_id';
    public $timestamps = false;

    protected $fillable = [
        'fo_is_video',
        'fo_filename',
        'fo_title',
        'fo_subtitle',
        'fo_text',
        'mand_id',
        'fo_sec_level',
        'fo_datetime',
        'db_saved',
        'fo_filepath',
        'fo_prefstat',
    ];

    protected $casts = [
        'fo_is_video' => 'boolean',
        'fo_datetime' => 'datetime',
        'db_saved'    => 'boolean',
    ];

    public function agFoContexts(): HasMany
    {
        return $this->hasMany(AgFoContext::class, 'fo_id', 'fo_id');
    }

    public function asgFoContexts(): HasMany
    {
        return $this->hasMany(AsgFoContext::class, 'fo_id', 'fo_id');
    }

    public function mpFoContexts(): HasMany
    {
        return $this->hasMany(MpFoContext::class, 'fo_id', 'fo_id');
    }

    public function activityGroups(): BelongsToMany
    {
        return $this->belongsToMany(ActivityGroup::class, 'ag_fo_context', 'fo_id', 'ag_id')
            ->withPivot(['ag_is_banner']);
    }

    public function activitySubgroups(): BelongsToMany
    {
        return $this->belongsToMany(ActivitySubgroup::class, 'asg_fo_context', 'fo_id', 'asg_id')
            ->withPivot(['ags_is_banner']);
    }

    public function mandProfiles(): BelongsToMany
    {
        return $this->belongsToMany(MandProfile::class, 'mp_fo_context', 'fo_id', 'mp_id');
    }
}
