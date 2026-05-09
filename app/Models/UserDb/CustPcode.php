<?php

namespace App\Models\UserDb;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustPcode extends UserDbModel
{
    protected $table = 'cust_pcode';
    protected $primaryKey = 'pcode_id';
    public $timestamps = false;

    protected $fillable = [
        'mand_id',
        'cust_id',
        'cust_passcode',
        'pcode_prefstat',
    ];

    public function mandUser(): BelongsTo
    {
        return $this->belongsTo(MandUser::class, 'mand_id', 'mand_id');
    }

    public function custUser(): BelongsTo
    {
        return $this->belongsTo(CustUser::class, 'cust_id', 'cust_id');
    }
}
