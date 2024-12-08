<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaypalSubscriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'paypal_id',
        'paypal_plan',
        'paypal_product'
    ];

}
