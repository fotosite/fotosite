<?php
/**
 * FILE:        app/Models/FotoDB/ActivitySubgroup.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   activityGroup()  — belongsTo ActivityGroup; reads fotodb.activity_group.*
 *              asgFoContexts()  — hasMany AsgFoContext; reads fotodb.asg_fo_context.*
 *              fotos()          — belongsToMany FotoObj via asg_fo_context; reads fotodb.foto_obj.*
 *
 * CALLS:       App\Models\FotoDB\ActivityGroup
 *              App\Models\FotoDB\AsgFoContext
 *              App\Models\FotoDB\FotoObj
 *
 * DB ACCESS:   fotodb.activity_subgroup.asg_id, asg_title, asg_subtitle, asg_text,
 *              asg_public, mand_id, asg_sec_level, ag_id, asg_prefstat, asg_date
 *              fotodb.asg_fo_context.ags_is_banner (pivot)
 *
 * CHANGES:     1.0.0 (2026-06-16) asg_sec_code → asg_sec_level (TINYINT UNSIGNED), Docblock ergänzt
 */

namespace App\Models\FotoDB;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActivitySubgroup extends FotoDbModel
{
    protected $table = 'activity_subgroup';
    protected $primaryKey = 'asg_id';
    public $timestamps = false;

    protected $fillable = [
        'asg_title',
        'asg_subtitle',
        'asg_text',
        'asg_public',
        'mand_id',
        'asg_sec_level',
        'ag_id',
        'asg_prefstat',
        'asg_date',
    ];

    protected $casts = [
        'asg_public' => 'boolean',
        'asg_date'   => 'date',
    ];

    public function activityGroup(): BelongsTo
    {
        return $this->belongsTo(ActivityGroup::class, 'ag_id', 'ag_id');
    }

    public function asgFoContexts(): HasMany
    {
        return $this->hasMany(AsgFoContext::class, 'asg_id', 'asg_id');
    }

    public function fotos(): BelongsToMany
    {
        return $this->belongsToMany(FotoObj::class, 'asg_fo_context', 'asg_id', 'fo_id')
            ->withPivot(['ags_is_banner']);
    }
}
