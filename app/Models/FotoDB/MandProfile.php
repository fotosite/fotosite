<?php

namespace App\Models\FotoDB;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MandProfile extends FotoDbModel
{
    protected $table = 'mand_profile';
    protected $primaryKey = 'mp_id';
    public $timestamps = false;

    protected $fillable = [
        'mand_id',
        'mp_name',
        'mp_title',
        'mp_text',
        'mp_title_start',
        'mp_subtitle_start',
    ];

    public function mpFoContexts(): HasMany
    {
        return $this->hasMany(MpFoContext::class, 'mp_id', 'mp_id');
    }

    public function fotos(): BelongsToMany
    {
        return $this->belongsToMany(FotoObj::class, 'mp_fo_context', 'mp_id', 'fo_id');
    }
}
