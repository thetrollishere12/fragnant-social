<?php

namespace App\Helper;

use GuzzleHttp\Client;

use Stripe\Stripe;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionItem;

use App\Models\PaypalSubscription;

use App\Models\User;
use Carbon\Carbon;

use App\Models\ApiCredentialKey;

use App\Models\SubscriptionProduct;
use App\Models\SubscriptionPlan;

use App\Helper\PaypalHelper;

use Auth;

class SubscriptionHelper
{







public static function is_subscribed($id){

    $paypal = PaypalSubscription::where('user_id',$id)->where('paypal_status','=','active')->where(function($query){
        $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
    })->get();

    $stripe = User::find($id)->subscriptions()->active()->get();

    $both = $paypal->merge($stripe);

    return $both;

}

public static function is_subscribed_type($id,$product_name){

        $product = SubscriptionProduct::where('name',$product_name)->first();

        $plan_name = SubscriptionPlan::where('subscription_product_id',$product->id)->pluck('name')->toArray();

        $paypal = PaypalSubscription::where('user_id',$id)->where('paypal_status','=','active')->whereIn('name',$plan_name)->where(function($query){
            $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
        })->get();

        $stripe = User::find($id)->subscriptions()->active()->whereIn('name',$plan_name)->get();

        $both = $paypal->merge($stripe);

        return $both;

}

public static function is_subscribed_to($id,$name){

    if (is_array($name)) {

        $paypal = PaypalSubscription::where('user_id',$id)->where('paypal_status','=','active')->whereIn('name',$name)->where(function($query){
            $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
        })->get();

        $stripe = User::find($id)->subscriptions()->active()->whereIn('name', $name)->get();

    }else{

        $paypal = PaypalSubscription::where('user_id',$id)->where('paypal_status','=','active')->where('name',$name)->where(function($query){
            $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
        })->get();

        $stripe = User::find($id)->subscriptions()->active()->where('name', $name)->get();

    }

    $both = $paypal->merge($stripe);

    return $both;

}

public static function user_is_subscribed_type($product_name){


        $product = SubscriptionProduct::where('name',$product_name)->first();

        $plan_name = SubscriptionPlan::where('subscription_product_id',$product->id)->pluck('name')->toArray();

        $paypal = PaypalSubscription::where('user_id',auth()->user()->id)->where('paypal_status','=','active')->whereIn('name',$plan_name)->where(function($query){
            $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
        })->get();

        $stripe = auth()->user()->subscriptions()->active()->whereIn('name',$plan_name)->get();

        $both = $paypal->merge($stripe);

        return $both;

}

public static function user_is_subscribed_to($name){

    if (is_array($name)) {
        
        $paypal = PaypalSubscription::where('user_id',auth()->user()->id)->where('paypal_status','=','active')->whereIn('name',$name)->where(function($query){
            $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
        })->get();

        $stripe = auth()->user()->subscriptions()->active()->whereIn('name', $name)->get();

    }else{

        $paypal = PaypalSubscription::where('user_id',auth()->user()->id)->where('paypal_status','=','active')->where('name',$name)->where(function($query){
            $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
        })->get();

        $stripe = auth()->user()->subscriptions()->active()->where('name', $name)->get();

    }

    $both = $paypal->merge($stripe);

    return $both;

}

public static function subscription_details($name,$product_name){

    $product = SubscriptionProduct::where('name',$product_name)->first();

    $plan = SubscriptionPlan::where('name',$name)->where('subscription_product_id',$product->id)->first();

    if ($plan) {
        $details = $plan;
    }else{
        $details = SubscriptionPlan::where(function ($query) use ($product) {
            $query->where('name', 'Personal')
                  ->where('subscription_product_id', $product->id);
        })
        ->orWhere('price', 0.00)
        ->orWhere('name', 'Free')
        ->first();
    }

    return $details;

}



public static function user_is_pastDue($product_name){

    $product = SubscriptionProduct::where('name',$product_name)->first();

    $plan_name = SubscriptionPlan::where('subscription_product_id',$product->id)->pluck('name')->toArray();

    $stripe = Auth::user()->subscriptions()->pastDue()->whereIn('name',$plan_name)->get();

    $both = $stripe;

    return $both;
}


public static function user_is_onGracePeriod($product_name){

    $product = SubscriptionProduct::where('name',$product_name)->first();

    $plan_name = SubscriptionPlan::where('subscription_product_id',$product->id)->pluck('name')->toArray();

    $stripe = Auth::user()->subscriptions()->active()->where('ends_at','!=',NULL)->where('ends_at','>',Carbon::today())->whereIn('name',$plan_name)->get();

    $paypal = PaypalSubscription::where('user_id',auth()->user()->id)->where('paypal_status','=','active')->where('ends_at','!=',NULL)->where('ends_at','>',Carbon::today())->whereIn('name',$plan_name)->get();

    $both = $paypal->merge($stripe);

    return $both;
}

public static function cancel_user_subscription($product_name){

    $subscription = self::user_is_subscribed_type($product_name)->first();

    switch ($subscription->payment_method) {
        case 'Stripe':

            Stripe::setApiKey(env('STRIPE_SECRET'));
            $stripe_name = $subscription->name;
            auth()->user()->subscription($stripe_name)->cancel();

            break;
        case 'Paypal':

            $bearer_token = PaypalHelper::paypal_bearer_token();

            $subscription_details = PaypalHelper::paypal_subscription($subscription->paypal_id,$bearer_token);

            $subscription->update([
                'ends_at'=>Carbon::parse($subscription_details->billing_info->next_billing_time)
            ]);

            PaypalHelper::paypal_subscription_suspend($subscription_details->id,$bearer_token);

            break;
        default:
        return response()->json(['message' => 'There was an issue with cancel_user_subscription 8331'],404);
        break;
    }
}

public static function resume_user_subscription($product_name){

    $subscription = self::user_is_subscribed_type($product_name)->first();

    switch ($subscription->payment_method) {
        case 'Stripe':

            Stripe::setApiKey(env('STRIPE_SECRET'));

            $stripe_name = $subscription->name;

            auth()->user()->subscription($stripe_name)->resume();

            break;
        case 'Paypal':

            $bearer_token = PaypalHelper::paypal_bearer_token();

            $subscription_details = PaypalHelper::paypal_subscription($subscription->paypal_id,$bearer_token);

            PaypalHelper::paypal_subscription_activate($subscription_details->id,$bearer_token);

            $subscription->update([
                'ends_at'=>null
            ]);

            break;

        default:
        return response()->json(['message' => 'There was an issue with resume_user_subscription 7331'],404);
        break;
    }

}







}