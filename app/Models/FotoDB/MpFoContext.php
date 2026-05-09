<?php

namespace App\Models\FotoDB;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpFoContext extends FotoDbModel
{
    protected $table = 'mp_fo_context';
    protected $primaryKey = 'mp_fo_id';
    public $timestamps = false;

    protected $fillable = [
        'mp_id',
        'fo_id',
    ];

    public function mandProfile(): BelongsTo
    {
        return $this->belongsTo(MandProfile::class, 'mp_id', 'mp_id');
    }

    public function fotoObj(): BelongsTo
    {
        return $this->belongsTo(FotoObj::class, 'fo_id', 'fo_id');
    }
}
