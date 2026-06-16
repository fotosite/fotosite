<?php
/**
 * FILE:        app/Models/FotoDB/ActivityGroup.php
 * VERSION:     1.0.2
 *
 * FUNCTIONS:   subgroups()    — hasMany ActivitySubgroup; reads fotodb.activity_subgroup.*
 *              agFoContexts() — hasMany AgFoContext; reads fotodb.ag_fo_context.*
 *              fotos()        — belongsToMany FotoObj via ag_fo_context; reads fotodb.foto_obj.*
 *
 * CALLS:       App\Models\FotoDB\ActivitySubgroup
 *              App\Models\FotoDB\AgFoContext
 *              App\Models\FotoDB\FotoObj
 *
 * DB ACCESS:   fotodb.activity_group.ag_id, ag_title, ag_subtitle, ag_text,
 *              mand_id, ag_sec_level, ag_sort_date, ag_prefstat
 *              fotodb.ag_fo_context.ag_is_banner (pivot)
 *
 * CHANGES:     1.0.1 (2026-06-14) ag_banner aus withPivot entfernt — DDL-Feld gelöscht
 *              1.0.2 (2026-06-16) ag_sec_code → ag_sec_level (TINYINT UNSIGNED), ag_sort_date ergänzt
 */

namespace App\Models\FotoDB;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActivityGroup extends FotoDbModel
{
    protected $table = 'activity_group';
    protected $primaryKey = 'ag_id';
    public $timestamps = false;

    protected $fillable = [
        'ag_title',
        'ag_subtitle',
        'ag_text',
        'mand_id',
        'ag_sec_level',
        'ag_sort_date',
        'ag_prefstat',
    ];

    public function subgroups(): HasMany
    {
        return $this->hasMany(ActivitySubgroup::class, 'ag_id', 'ag_id');
    }

    public function agFoContexts(): HasMany
    {
        return $this->hasMany(AgFoContext::class, 'ag_id', 'ag_id');
    }

    public function fotos(): BelongsToMany
    {
        return $this->belongsToMany(FotoObj::class, 'ag_fo_context', 'ag_id', 'fo_id')
            ->withPivot(['ag_is_banner']);
    }
}
