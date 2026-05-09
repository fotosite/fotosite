<?php

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
        'ag_sec_code',
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
            ->withPivot(['ag_banner', 'ag_is_banner']);
    }
}
