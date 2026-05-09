<?php

namespace App\Models\UserDb;

use Illuminate\Database\Eloquent\Model;

abstract class UserDbModel extends Model
{
    protected $connection = 'userdb';
}
