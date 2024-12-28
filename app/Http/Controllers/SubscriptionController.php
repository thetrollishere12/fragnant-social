<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;

use Stripe\Stripe;
use Stripe\PaymentIntent;

use Auth;
use Laravel\Cashier\PaymentMethod;
use App\Models\User;

use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionItem;

use App\Models\PaypalSubscription;
use App\Models\PaypalSubscriptionItem;
use Carbon\Carbon;
use GuzzleHttp\Client;

use App\Helper\PaypalHelper;
use App\Helper\SubscriptionHelper;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionProduct;

class SubscriptionController extends Controller
{
 



public function pricing(){
        return view('subscription.pricing',[
            'products'=>SubscriptionProduct::where('status', 1)->get()
        ]);
    }



    public function upgrade(Request $req, $subscription_id){

        try{

            $plan = SubscriptionPlan::find($subscription_id);

            if (SubscriptionHelper::subscribed_to_plan(Auth::user()->id, $plan->name)->count() > 0) {
                return redirect('subscription-pricing');
            }

            return view('subscription.upgrade',["plan"=>$plan]);

        }catch(\Exception $e){

            return redirect('subscription-pricing');

        }

    }

    public function change(Request $req, $subscription_id){

        try{

            $plan = SubscriptionPlan::find($subscription_id);

            if (SubscriptionHelper::subscribed_to_plan(Auth::user()->id, $plan->name)->count() > 0) {
                return redirect('subscription-pricing');
            }

            return view('subscription.change',["plan"=>$plan]);

        }catch(\Exception $e){

            return redirect('subscription-pricing');

        }

    }






   public function stripe_payment_subscription_v2(Request $req, $plan_id){

        if (!$req->payment_intent) {
            return redirect('subscription-pricing');
        }

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        try {
            $paymentIntent = $stripe->paymentIntents->retrieve($req->payment_intent);

            if (!$paymentIntent) {
                return redirect('subscription-api-pricing');
            }

            $user = $req->user();
            $user->createOrGetStripeCustomer();
            $user->updateDefaultPaymentMethod($paymentIntent->payment_method);

            $plan = SubscriptionPlan::findOrFail($plan_id);

            $subscriptionData = [
                'default_payment_method' => $paymentIntent->payment_method,
            ];

            // Check for trial eligibility
            if ($plan->trial_period_days) {
                $hasUsedTrial = SubscriptionHelper::has_used_free_trial($user->id, $plan->subscription_product_id);
                $subscriptionData['trial_end'] = $hasUsedTrial ? 'now' : now()->addDays($plan->trial_period_days)->timestamp;
            }

            // Retrieve the active subscription
            $activeSubscription = $stripe->subscriptions->all([
                'status' => 'active',
                'limit' => 1,
                'customer' => $user->stripe_id,
            ])->data[0] ?? null;

            if (!$activeSubscription) {
                throw new \Exception('No active subscription found for the user.');
            }

            // Update the subscription on Stripe
            $stripe->subscriptions->update($activeSubscription->id, $subscriptionData);

            // Save subscription data locally
            $newSubscription = Subscription::firstOrCreate(
                ['stripe_id' => $activeSubscription->id],
                [
                    'user_id' => $user->id,
                    'type' => $plan->name,
                    'stripe_price' => $plan->stripe_plan_id,
                    'quantity' => 1,
                    'payment_method' => 'Stripe',
                    'stripe_status' => 'active',
                    'trial_ends_at' => $plan->trial_period_days && !$hasUsedTrial ? now()->addDays($plan->trial_period_days) : null,
                ]
            );

            // Save subscription item data locally
            $subscriptionItem = $activeSubscription->items->data[0];

            SubscriptionItem::firstOrCreate(
                ['stripe_id' => $subscriptionItem->id],
                [
                    'subscription_id' => $newSubscription->id,
                    'stripe_product' => $subscriptionItem->plan->product,
                    'stripe_price' => $subscriptionItem->plan->id,
                    'quantity' => 1,
                ]
            );

        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect('user/subscription');

    }










    public function change_subscription(Request $req){

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));


        $plan = SubscriptionPlan::find($req->plan_id);

        $product = SubscriptionProduct::find($plan->subscription_product_id);

        $subscription = SubscriptionHelper::subscribed_to_product(Auth::user()->id, $product->name)->first();


        if ($subscription) {
            
            if ($subscription->payment_method == "Stripe") {
        


                $activeSubscription = Auth::user()->subscriptions($plan->name)->active()->first();

                if (!$activeSubscription) {
                    return response()->plan(['error' => 'No active subscription found'], 404);
                }

                



                 // Check if the plan has a trial period
                if ($activeSubscription->trial_ends_at && $plan->trial_period_days) {
                    // Calculate the trial end date
                    $trialEndTimestamp = $activeSubscription->trial_ends_at
                        ? $activeSubscription->trial_ends_at->timestamp
                        : now()->addDays($plan->trial_period_days)->timestamp;

                    // Swap the subscription and invoice immediately
                    Auth::user()
                        ->subscription($activeSubscription->type)
                        ->swapAndInvoice($plan['stripe_plan_id']);

                    // Update the local subscription type and trial end date
                    $activeSubscription->update([
                        'type' => $plan['name'],
                        'trial_ends_at' => $trialEndTimestamp,
                    ]);

                    $stripe->subscriptions->update($activeSubscription->stripe_id, [
                        'trial_end' => $trialEndTimestamp,
                    ]);

                } elseif ($activeSubscription->trial_ends_at) {
                    // End the active trial immediately
                    Auth::user()
                        ->subscription($activeSubscription->type)
                        ->swapAndInvoice($plan['stripe_plan_id']);

                    // Update the local subscription to reflect the trial has ended
                    $activeSubscription->update([
                        'type' => $plan['name'],
                        'trial_ends_at' => now()->timestamp, // Clear the trial end date
                    ]);

                    $stripe->subscriptions->update($activeSubscription->stripe_id, [
                       'trial_end' => now()->timestamp, // End the trial immediately
                    ]);

                } else {
                    // Swap the subscription normally if there's no active trial
                    Auth::user()
                        ->subscription($activeSubscription->type)
                        ->swapAndInvoice($plan['stripe_plan_id']);

                    // Update the local subscription type
                    $activeSubscription->update([
                        'type' => $plan['name']
                    ]);

                }

    


            }elseif($subscription->payment_method == "Paypal") {
                
                $bearer_token = PaypalHelper::paypal_bearer_token();

                $revised = PaypalHelper::paypal_subscription_revise($subscription->paypal_id,$bearer_token,$plan['paypal_plan_id']);

                PaypalSubscription::where('paypal_id',$subscription->paypal_id)->update([
                    'name'=>$value["name"],
                    'paypal_plan'=>$revised->plan_id
                ]);

                PaypalSubscriptionItem::where('paypal_id',$subscription->paypal_id)->update([
                    'paypal_product'=>$revised->plan_id,
                    'paypal_plan'=>$revised->plan_id
                ]);

            }

            return redirect('user/subscription');

        }else{

        }
        

    }




    // Paypal

    public function paypal_payment_subscription(Request $req){

        $bearer_token = PaypalHelper::paypal_bearer_token();

        $data = PaypalHelper::paypal_subscription($req->sub_id,$bearer_token);

        if ($data->status === 'ACTIVE' && $req->sub_id === $data->id) {

            User::where('id', '=', Auth::id())->update([
                'paypal_id'=>$data->subscriber->payer_id,
                'paypal_email'=>$data->subscriber->email_address
            ]);

            $subscription = new PaypalSubscription;
            $subscription->user_id = Auth::id();
            $subscription->name = $req->name;
            $subscription->paypal_id = $data->id;
            $subscription->paypal_status = $data->status;
            $subscription->paypal_plan = $data->plan_id;
            $subscription->quantity = $data->quantity;
            $subscription->trial_ends_at = null;
            $subscription->ends_at = null;
            $subscription->payment_method = "Paypal";
            $subscription->save();


            $item = new PaypalSubscriptionItem;
            $item->subscription_id = $subscription->id;
            $item->paypal_id = $data->id;
            $item->paypal_product = $data->plan_id;
            $item->paypal_plan = $data->plan_id;
            $item->quantity = $data->quantity;
            $item->save();

            return response()->json(['message' => 'Subscribed'],200);

        }else{

            return response()->json(['message' => 'There was an error with subscription'],404);

        }

    }



    public function stripe_subscription_payment_update(Request $request){


        Auth::user()->updateDefaultPaymentMethod($request->paymentMethod);

        return Redirect(url('/user/subscription'))->with('success','Payment method has been updated.');

    }








// public function stripe_payment_subscription(Request $req)
// {
//     Stripe::setApiKey(env('STRIPE_SECRET'));

//     $user = $req->user();

//     try {
//         // Ensure the Stripe customer exists
//         $user->createOrGetStripeCustomer();

//         // Update the default payment method
//         $user->updateDefaultPaymentMethod($req->paymentMethod);

//         // Retrieve the subscription plan
//         $plan = SubscriptionPlan::findOrFail($req->plan_id);

//         // Check if the user is eligible for a trial
//         $trialDays = $plan->trial_period_days ?? 0;
//         $hasUsedTrial = SubscriptionHelper::has_used_free_trial($user->id,$plan->subscription_product_id);

//         // Adjust trial period if already used
//         $trialEnd = $hasUsedTrial ? null : now()->addDays($trialDays);

//         // Create a new subscription
//         $newSubscription = $user->newSubscription($plan->name, $plan->stripe_plan_id)
//             ->trialUntil($trialEnd)
//             ->create($req->paymentMethod, [
//                 'email' => $user->email,
//             ]);

//         // Update subscription with additional metadata
//         $newSubscription->update([
//             'payment_method' => 'Stripe',
//         ]);

//         return redirect('user/subscription')->with('success', 'Subscription created successfully.');

//     } catch (\Exception $e) {
//         return back()->withErrors($e->getMessage());
//     }
// }






}
