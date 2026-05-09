<?php

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
        'fo_sec_code',
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
            ->withPivot(['ag_banner', 'ag_is_banner']);
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
