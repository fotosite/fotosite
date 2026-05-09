<?php

namespace App\Models\FotoBlobDb;

use Illuminate\Database\Eloquent\Model;

abstract class FotoBlobDbModel extends Model
{
    protected $connection = 'fotoblobdb';
}
