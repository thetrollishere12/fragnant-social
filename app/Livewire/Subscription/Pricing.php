<?php

namespace App\Livewire\Subscription;

use Livewire\Component;
use Storage;
use Auth;

use App\Models\SubscriptionProduct;
use App\Models\SubscriptionPlan;

use App\Helper\SubscriptionHelper;

class Pricing extends Component
{

    public $subscription;
    public $type;

    public $types;

    public $plan_name;

    public $product_name;

    public $product_id;

    public function mount(){

        $this->product_id = SubscriptionProduct::where('name',$this->product_name)->value('id');

        $this->types = SubscriptionPlan::where('subscription_product_id', $this->product_id)->whereNotNull('recurring_type')->get()->unique('recurring_type')->pluck('recurring_type');
        
        $this->type = $this->types->first();

        $this->plan_name = SubscriptionPlan::where('subscription_product_id', $this->product_id)->get()->unique('name')->pluck('name')->toArray();

    }

    public function subscription_type($type){
        $this->type = $type;
    }

    public function render()
    {

        $user_subbed = null;

        if (Auth::user()) {
            $user_subbed = SubscriptionHelper::subscribed_to_plan(Auth::user()->id, $this->plan_name)->first();
        }

        $subscriptions = SubscriptionPlan::where('subscription_product_id', $this->product_id)
            ->where(function($query) {
                $query->where('recurring_type', $this->type)
                      ->orWhereNull('recurring_type');
            })->orderBy('price')->get();

        return view('livewire.subscription.pricing',[
            'user_subbed'=>$user_subbed,
            'subscriptions'=>$subscriptions
        ]);

    }
}
