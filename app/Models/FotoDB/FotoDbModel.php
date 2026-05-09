<?php

namespace App\Models\FotoDB;

use Illuminate\Database\Eloquent\Model;

abstract class FotoDbModel extends Model
{
    protected $connection = 'fotodb';
}
