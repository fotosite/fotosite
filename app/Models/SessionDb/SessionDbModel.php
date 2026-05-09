<?php

namespace App\Models\SessionDb;

use Illuminate\Database\Eloquent\Model;

abstract class SessionDbModel extends Model
{
    protected $connection = 'sessiondb';
}
