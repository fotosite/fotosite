<?php

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
        'asg_sec_code',
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
