<?php

namespace App\Livewire\Stripe;

use Livewire\Component;
use App\Models\SubscriptionPlan;
use Auth;
use App\Helper\SubscriptionHelper;

class Subscription extends Component
{


    public $plan,$payment_intent;

    public function mount($plan_id){

        try{

            $this->plan = SubscriptionPlan::find($plan_id);

        if (SubscriptionHelper::user_is_subscribed_to($this->plan->name)->count() > 0) {
            return redirect('subscription-api-pricing');
        }

        }catch(\Exception $e){
            return redirect('subscription-api-pricing');
        }







        try{


        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

            $user = Auth::user();

            $user->createOrGetStripeCustomer();

            $stripe_subscription = $stripe->subscriptions->create([
                'customer' => $user->stripe_id,
                'items' => [[
                    'price' => $this->plan['stripe_plan_id'],
                ]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription'
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            $this->payment_intent = $stripe_subscription->latest_invoice->payment_intent->client_secret;

        }catch(\Exception $e){

            echo $e->getMessage();

        }




    }











    public function render()
    {
        return view('livewire.stripe.subscription');
    }
}
