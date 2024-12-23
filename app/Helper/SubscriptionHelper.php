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



use App\Models\UserMedia;
use App\Models\PublishedMedia;
use App\Models\DigitalAsset;


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

        $stripe = User::find($id)->subscriptions()->active()->whereIn('type',$plan_name)->get();

        $both = $paypal->merge($stripe);

        return $both;

}

public static function is_subscribed_to($id,$name){

    if (is_array($name)) {

        $paypal = PaypalSubscription::where('user_id',$id)->where('paypal_status','=','active')->whereIn('name',$name)->where(function($query){
            $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
        })->get();

        $stripe = User::find($id)->subscriptions()->active()->whereIn('type', $name)->get();

    }else{

        $paypal = PaypalSubscription::where('user_id',$id)->where('paypal_status','=','active')->where('name',$name)->where(function($query){
            $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
        })->get();

        $stripe = User::find($id)->subscriptions()->active()->where('type', $name)->get();

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

        $stripe = auth()->user()->subscriptions()->active()->whereIn('type',$plan_name)->get();

        $both = $paypal->merge($stripe);

        return $both;

}

public static function user_is_subscribed_to($name){

    if (is_array($name)) {
        
        $paypal = PaypalSubscription::where('user_id',auth()->user()->id)->where('paypal_status','=','active')->whereIn('name',$name)->where(function($query){
            $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
        })->get();

        $stripe = auth()->user()->subscriptions()->active()->whereIn('type', $name)->get();

    }else{

        $paypal = PaypalSubscription::where('user_id',auth()->user()->id)->where('paypal_status','=','active')->where('name',$name)->where(function($query){
            $query->where('ends_at','>',Carbon::today())->orWhere('ends_at',NULL);
        })->get();

        $stripe = auth()->user()->subscriptions()->active()->where('type', $name)->get();

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

    $stripe = Auth::user()->subscriptions()->pastDue()->whereIn('type',$plan_name)->get();

    $both = $stripe;

    return $both;
}


public static function user_is_onGracePeriod($product_name){

    $product = SubscriptionProduct::where('name',$product_name)->first();

    $plan_name = SubscriptionPlan::where('subscription_product_id',$product->id)->pluck('name')->toArray();

    $stripe = Auth::user()->subscriptions()->active()->where('ends_at','!=',NULL)->where('ends_at','>',Carbon::today())->whereIn('type',$plan_name)->get();

    $paypal = PaypalSubscription::where('user_id',auth()->user()->id)->where('paypal_status','=','active')->where('ends_at','!=',NULL)->where('ends_at','>',Carbon::today())->whereIn('name',$plan_name)->get();

    $both = $paypal->merge($stripe);

    return $both;
}

public static function cancel_user_subscription($product_name){

    $subscription = self::user_is_subscribed_type($product_name)->first();

    switch ($subscription->payment_method) {
        case 'Stripe':

            Stripe::setApiKey(env('STRIPE_SECRET'));
            $stripe_name = $subscription->type;
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

            $stripe_name = $subscription->type;

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






// Custom











    /**
     * Get the current storage used by the user.
     */
    public static function getCurrentStorageUsed(int $userId, int $digitalAssetId = null): int
    {
        // If a specific digital asset ID is provided
        if ($digitalAssetId) {
            // Fetch the storage for the specified digital asset owned by the user
            return UserMedia::where('digital_asset_id', $digitalAssetId)
                ->whereHas('digitalAsset', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->sum('size');
        }

        // Fetch IDs of all digital assets owned by the user
        $digitalAssets = DigitalAsset::where('user_id', $userId)->pluck('id');

        // If no digital assets are found, return 0
        if ($digitalAssets->isEmpty()) {
            return 0;
        }

        // Sum up the sizes for all digital assets owned by the user
        return UserMedia::whereIn('digital_asset_id', $digitalAssets)->sum('size');
    }



    /**
     * Get the maximum storage limit for the user.
     */
    public static function getMaxStorageLimit(int $userId): int
    {
        $subscriptions = self::is_subscribed($userId);

        if ($subscriptions->isEmpty()) {
            
            $plan = SubscriptionPlan::where('name', 'personal')->first();

            $maxStorageGB = $plan->plan_metadata['storage']; // Use metadata or default

            // Convert GB to bytes
            return $maxStorageGB * 1024 * 1024 * 1024;

        }else{

            $subscription = $subscriptions->first();
            $planName = $subscription->type;
            $plan = SubscriptionPlan::where('name', $planName)->first();

            $maxStorageGB = $plan->plan_metadata['storage']; // Use metadata or default

            // Convert GB to bytes
            return $maxStorageGB * 1024 * 1024 * 1024;

        }

        
    }

    /**
     * Check if the user has exceeded the storage limit.
     */
    public static function hasExceededStorageLimit(int $userId): bool
    {
        $currentStorage = self::getCurrentStorageUsed($userId);
        $maxStorage = self::getMaxStorageLimit($userId);

        return $currentStorage >= $maxStorage;
    }











    /**
 * Get the current monthly video upload count for the user.
 *
 * @param int $userId
 * @param int|null $year
 * @param int|null $month
 * @param int|null $digitalAssetId
 * @return int
 */
public static function getMonthlyVideoCount(int $userId, ?int $year = null, ?int $month = null, ?int $digitalAssetId = null): int
{
    $year = $year ?? now()->year;   // Default to the current year
    $month = $month ?? now()->month; // Default to the current month

    // If a specific digital asset ID is provided
    if ($digitalAssetId) {
        return PublishedMedia::where('digital_asset_id', $digitalAssetId)
            ->whereHas('digitalAsset', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get()
            ->count();
    }

    // Fetch IDs of all digital assets owned by the user
    $digitalAssets = DigitalAsset::where('user_id', $userId)->pluck('id');

    if ($digitalAssets->isEmpty()) {
        return 0;
    }

    // Count the videos for all digital assets
    return PublishedMedia::whereIn('digital_asset_id', $digitalAssets)
        ->whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->get()
        ->count();
}



    

    /**
     * Get the monthly video upload limit for the user.
     */
    public static function getMaxMonthlyVideoLimit(int $userId): int
    {
        $subscriptions = self::is_subscribed($userId);

        if ($subscriptions->isEmpty()) {
            
            $plan = SubscriptionPlan::where('name', 'personal')->first();

            return $plan->plan_metadata['published']; // Use metadata or default

        }else{

            $subscription = $subscriptions->first();
            $planName = $subscription->type;
            $plan = SubscriptionPlan::where('name', $planName)->first();

            return $plan->plan_metadata['published']; // Use metadata or default

        }

        
    }

    /**
     * Check if the user has exceeded the monthly video upload limit.
     */
    public static function hasExceededMonthlyVideoLimit(int $userId): bool
    {
        $currentVideoCount = self::getMonthlyVideoCount($userId);
        $maxVideoLimit = self::getMaxMonthlyVideoLimit($userId);

        return $currentVideoCount >= $maxVideoLimit;
    }






}