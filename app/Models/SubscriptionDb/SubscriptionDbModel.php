<?php

namespace App\Models\SubscriptionDb;

use Illuminate\Database\Eloquent\Model;

abstract class SubscriptionDbModel extends Model
{
    protected $connection = 'subscriptiondb';
}
