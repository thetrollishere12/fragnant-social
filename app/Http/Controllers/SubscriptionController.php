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
            'products'=>SubscriptionProduct::all()
        ]);
    }



    public function upgrade(Request $req, $subscription_id){

        try{

            $plan = SubscriptionPlan::find($subscription_id);

            if (SubscriptionHelper::user_is_subscribed_to($plan->name)->count() > 0) {
                return redirect('subscription-api-pricing');
            }

            return view('subscription.upgrade',["plan"=>$plan]);

        }catch(\Exception $e){

            return redirect('subscription-api-pricing');

        }

    }

    public function change(Request $req){

        $json = SubscriptionPlan::find($req->plan_id);

        $product = SubscriptionProduct::find($json->subscription_product_id);

        $subscription = SubscriptionHelper::user_is_subscribed_type($product->name)->first();

        if ($subscription->name == $json->name) {
            return redirect(url('subscription-api-pricing'));
        }

        return view('subscription.change',["plan"=>$json,"req"=>$req]);

    }





    public function stripe_payment_subscription(Request $req){

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $user = $req->user();

        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod($req->paymentMethod);

        $json = SubscriptionPlan::find($req->plan_id);

        $new_subscription = auth()->user()->newSubscription($json->name, $json->stripe_plan_id)->create($req->paymentMethod,[
            'email'=>$user->email
        ]);

        $new_subscription->update([
            'payment_method'=>'Stripe'
         ]);

        return redirect('user/developer');

    }


    public function stripe_payment_subscription_v2(Request $req, $plan_id){


        if(!$req->payment_intent){
            return redirect('subscription-api-pricing');
        }


        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        $paymentIntent = $stripe->paymentIntents->retrieve($req->payment_intent, []);


        if (!$paymentIntent) {
            return redirect('subscription-api-pricing');
        }

        try{

            $user = $req->user();

            $user->createOrGetStripeCustomer();

            $user->updateDefaultPaymentMethod($paymentIntent->payment_method);

            $plan = SubscriptionPlan::find($plan_id);

            $stripe_subscription = $stripe->subscriptions->all([
                'status' => 'active',
                'limit' => 1,
                'customer' => $user->stripe_id
            ])->data[0];

            $stripe->subscriptions->update($stripe_subscription->id,
                [
              'default_payment_method'=>$paymentIntent->payment_method
            ]);

            $new_subscription = Subscription::firstOrcreate([
                'stripe_id'=>$stripe_subscription->id
            ],
            [
                'user_id'=>$user->id,
                'type'=>$plan->name,
                'stripe_id'=>$stripe_subscription->id,
                'stripe_price'=>$plan->stripe_plan_id,
                'quantity'=>1,
                'payment_method'=>'Stripe',
                'stripe_status'=>'active'
            ]);

            SubscriptionItem::firstOrcreate([
                'stripe_id'=>$stripe_subscription->items->data[0]->id,
            ],[
                'subscription_id'=>$new_subscription->id,
                'stripe_id'=>$stripe_subscription->items->data[0]->id,
                'stripe_product'=>$stripe_subscription->items->data[0]->plan->product,
                'stripe_price'=>$stripe_subscription->items->data[0]->plan->id,
                'quantity'=>1
            ]);


        }catch(\Exception $e){


            return back()->withErrors($e->getMessage());

            
        }
        
        return redirect('user/developer');

    }



    public function change_subscription(Request $req){

        Stripe::setApiKey(env('STRIPE_SECRET'));


        $json = SubscriptionPlan::find($req->plan_id);

        $product = SubscriptionProduct::find($json->subscription_product_id);

        $subscription = SubscriptionHelper::user_is_subscribed_type($product->name)->first();


        if ($subscription) {
            
            if ($subscription->payment_method == "Stripe") {
        
                Auth::user()->subscription(Auth::user()->subscriptions()->active()->get()->first()->name)->swapAndInvoice($json['stripe_plan_id']);
                Auth::user()->subscriptions()->active()->get()->first()->update(["name"=>$json['name']]);

            }elseif($subscription->payment_method == "Paypal") {
                
                $bearer_token = PaypalHelper::paypal_bearer_token();

                $revised = PaypalHelper::paypal_subscription_revise($subscription->paypal_id,$bearer_token,$json['paypal_plan_id']);

                PaypalSubscription::where('paypal_id',$subscription->paypal_id)->update([
                    'name'=>$value["name"],
                    'paypal_plan'=>$revised->plan_id
                ]);

                PaypalSubscriptionItem::where('paypal_id',$subscription->paypal_id)->update([
                    'paypal_product'=>$revised->plan_id,
                    'paypal_plan'=>$revised->plan_id
                ]);

            }

            return redirect('user/developer');

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

        return Redirect(url('/user/developer'))->with('success','Payment method has been updated.');

    }




}
