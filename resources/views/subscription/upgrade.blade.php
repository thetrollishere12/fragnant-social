@extends('layouts.payment')

@section('title')
Upgrade Subscription
@endsection

@section('main')

<link rel="stylesheet" type="text/css" href="{{ asset('css/billing.css?'.time().'') }}" rel="stylesheet">

<main class="grid m-auto h-full max-w-7xl grid-cols-1 md:grid-cols-2">
    <div class="shopping-cart-section pb-32 p-7 lg:p-20">
        <div class="mb-2 hidden md:block">
            <x-application-mark class="h-9 w-auto" />
        </div>
        <x-errors title="Warning there's an error"/>
        <div class="full-cart-price">
            <h2 class="font-bold text-4xl text-black">
                ${{ $plan['sale_price'] ? $plan['sale_price'] : $plan['price'] }}/{{$plan['recurring_type']}}
            </h2>
        </div>

        @if($plan['trial_period_days'] && (!\App\Helper\SubscriptionHelper::has_used_free_trial(auth()->user()->id,$plan['subscription_product_id'])))
        <div class="mt-2 text-sm text-indigo-600 font-medium">
            Start your {{ $plan['trial_period_days'] }}-day free trial
        </div>
        @elseif($plan['trial_period_days'])
        <div class="mt-2 text-sm text-gray-500 font-medium">
            You have already used your free trial
        </div>
        @endif
        
        <div class="shopping-cart-container">
            <div class="cart-product-container">      
                <div class="flex">
                    <div class="grid grid-cols-3 items-center">
                        <div class="pr-8 py-4">
                            <img class="" src="{{ Storage::disk('public')->url('image/subscription/'.$plan['icon_image']) }}">
                        </div>
                        <div class="text-xs font-bold col-span-2 pr-1">
                            <div class="capitalize">@if(isset($count)){{ $count }}x @endif{{ $plan['name'] }}</div>
                            <div class="dropdown-name">${{$plan['sale_price'] ? $plan['sale_price'] : $plan['price']}}/{{$plan['recurring_type']}}</div>
                            <div></div>
                        </div>
                    </div>
                    <div class="flex text-sm items-center justify-end">
                        <div><b>${{$plan['sale_price'] ? $plan['sale_price'] : $plan['price']}}/{{$plan['recurring_type']}}</b></div>
                    </div>
                </div>
            </div>
            <div class="flex font-bold py-2">
                <div class="w-full">Total Due</div>
                <div class="text-right w-full total-cost">${{$plan['sale_price'] ? $plan['sale_price'] : $plan['price']}}/{{$plan['recurring_type']}}</div>
            </div>
        </div>
        <div class="mt-4 text-right">
            <a href="{{ url('terms-condition') }}" class="text-xs text-gray-400 pl-5 transition">Terms</a>
            <a href="{{ url('privacy-policy') }}" class="text-xs text-gray-400 pl-5 transition">Privacy</a>
        </div>
    </div>

<div class="order-first md:order-last" style="box-shadow: -6px 0 16px -9px rgb(0 0 0 / 10%);">

    @livewire('stripe.subscription',['plan_id'=> $plan->id])
    
</div>

</main>

@endsection
