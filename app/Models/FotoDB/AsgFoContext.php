<?php

namespace App\Models\FotoDB;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsgFoContext extends FotoDbModel
{
    protected $table = 'asg_fo_context';
    protected $primaryKey = 'asg_fo_id';
    public $timestamps = false;

    protected $fillable = [
        'asg_id',
        'fo_id',
        'ags_is_banner',
    ];

    protected $casts = [
        'ags_is_banner' => 'boolean',
    ];

    public function activitySubgroup(): BelongsTo
    {
        return $this->belongsTo(ActivitySubgroup::class, 'asg_id', 'asg_id');
    }

    public function fotoObj(): BelongsTo
    {
        return $this->belongsTo(FotoObj::class, 'fo_id', 'fo_id');
    }
}
