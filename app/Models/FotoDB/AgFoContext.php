<?php

namespace App\Models\FotoDB;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgFoContext extends FotoDbModel
{
    protected $table = 'ag_fo_context';
    protected $primaryKey = 'ag_fo_id';
    public $timestamps = false;

    protected $fillable = [
        'ag_banner',
        'ag_id',
        'fo_id',
        'ag_is_banner',
    ];

    protected $casts = [
        'ag_banner'    => 'boolean',
        'ag_is_banner' => 'boolean',
    ];

    public function activityGroup(): BelongsTo
    {
        return $this->belongsTo(ActivityGroup::class, 'ag_id', 'ag_id');
    }

    public function fotoObj(): BelongsTo
    {
        return $this->belongsTo(FotoObj::class, 'fo_id', 'fo_id');
    }
}
