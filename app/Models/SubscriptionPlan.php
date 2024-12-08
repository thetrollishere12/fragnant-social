<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\SubscriptionProduct;
use Orchid\Screen\AsSource;




class SubscriptionPlan extends Model
{
    use HasFactory,AsSource;

    protected $fillable = [
        'subscription_product_id',
        'recurring_count',
        'recurring_type',
        'name',
        'icon_image',
        'payment_type',
        'sale_price',
        'price',
        'currency',
        'stripe_plan_id',
        'paypal_plan_id',
        'benefits',
        'bandwidth',
        'trial_period_days',
        'exclusive_to_user_id',
        'status',
        'images',
        'icon_image',
        'description',
        'plan_metadata',
        'attributes',
        'public'
    ];

    protected $casts = [
        'benefits'=>'array',
        'exclusive_to_user_id'=>'array',
        'status'=>'boolean',
        'images'=>'array',
        'plan_metadata'=>'array',
        'attributes'=>'array',
        'public'=>'boolean'
    ];

    public function product(){
        return $this->hasOne(SubscriptionProduct::class,'id','subscription_product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }


}
