<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\SubscriptionPlan;

class SubscriptionProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'description',
        'stripe_product_id',
        'paypal_product_id',
        'image'
    ];

    protected $casts = [
        'image'=>'array',
        'status'=>'boolean'
    ];

    public function plan(){
        return $this->hasMany(SubscriptionPlan::class,'subscription_product_id','id');
    }

}
