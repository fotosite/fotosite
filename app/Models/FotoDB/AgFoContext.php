<?php
/**
 * FILE:        app/Models/FotoDB/AgFoContext.php
 * VERSION:     1.0.1
 *
 * FUNCTIONS:   activityGroup() — BelongsTo ActivityGroup; reads fotodb.ag_fo_context.*
 *              fotoObj()       — BelongsTo FotoObj; reads fotodb.ag_fo_context.*
 *
 * CALLS:       App\Models\FotoDB\ActivityGroup
 *              App\Models\FotoDB\FotoObj
 *
 * DB ACCESS:   fotodb.ag_fo_context.ag_fo_id, ag_id, fo_id, ag_is_banner
 *
 * CHANGES:     1.0.1 (2026-06-14) ag_banner entfernt — DDL-Feld gelöscht
 */

namespace App\Models\FotoDB;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgFoContext extends FotoDbModel
{
    protected $table = 'ag_fo_context';
    protected $primaryKey = 'ag_fo_id';
    public $timestamps = false;

    protected $fillable = [
        'ag_id',
        'fo_id',
        'ag_is_banner',
    ];

    protected $casts = [
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
