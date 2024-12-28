@extends('layouts.payment')

@section('title')
Change Subscription
@endsection

@section('main')
<link rel="stylesheet" type="text/css" href="{{ asset('css/billing.css?'.time().'') }}" rel="stylesheet">

<main class="grid m-auto h-full max-w-5xl grid-cols-1 md:grid-cols-2">
    
    <div class="shopping-cart-section pb-32 p-7 lg:p-20">
        
        <div class="mb-2 hidden md:block">
            <x-application-mark class="h-9 w-auto" />
        </div>
        <x-errors title="Warning there's an error"/>

        <div class="full-cart-price">
            <h2 class="font-bold text-4xl text-black">
                ${{ isset($plan['sale_price']) ? $plan['sale_price'] : $plan['price'] }}/{{ ucwords($plan['recurring_type']) }}
            </h2>
        </div>

        <div class="shopping-cart-container">

            <div class="cart-product-container">      

                <div class="flex">
                    <div class="grid grid-cols-3 items-center">
                        <div class="pr-8 py-4">
                            <img class="" src="{{ Storage::disk('public')->url('image/subscription/'.$plan['image']) }}">
                        </div>
                        <div class="text-xs font-bold col-span-2 pr-1">
                            <div class="capitalize">@if(isset($count)){{ $count }}x @endif{{ $plan['name'] }}</div>
                            <div class="dropdown-name">${{ isset($plan['sale_price']) ? $plan['sale_price'] : $plan['price'] }}/{{ ucwords($plan['recurring_type']) }}</div>
                            <div></div>
                        </div>
                    </div>
                    <div class="flex text-sm items-center justify-end">
                        <div><b>${{ isset($plan['sale_price']) ? $plan['sale_price'] : $plan['price'] }}/{{ ucwords($plan['recurring_type']) }}</b></div>
                    </div>
                </div>

            </div>

            <div class="flex font-bold py-2">
               <div class="w-full">Total Due</div>
               <div class="text-right w-full total-cost">${{ isset($plan['sale_price']) ? $plan['sale_price'] : $plan['price'] }}/{{ ucwords($plan['recurring_type']) }}</div>
            </div>

        </div>

        <div class="mt-4 text-right">
            <a href="{{ url('terms-condition') }}" class="text-xs text-gray-400 pl-5 transition">Terms</a>
            <a href="{{ url('privacy-policy') }}" class="text-xs text-gray-400 pl-5 transition">Privacy</a>
        </div>

    </div>

    <form class="order-first md:order-last" action="{{ url('subscription-init-change') }}" method="POST" id="addPaymentFrm" style="box-shadow: -6px 0 16px -9px rgb(0 0 0 / 10%);">
        @csrf

            <div class="md:hidden block bg-gray-100 py-2.5 px-4">
                <x-application-mark class="block h-9 w-auto" />
                <h2 class="font-bold text-4xl text-black">
                    ${{ isset($plan['sale_price']) ? $plan['sale_price'] : $plan['price'] }}/{{ ucwords($plan['recurring_type']) }}
                </h2>
            </div>

            <div class="md:hidden block bg-gray-100 border-t py-2.5 px-4">
                <h2 class="font-bold text-2xl text-black">Payment</h2>
            </div>

            <div class="right-section h-full md:h-screen p-7 lg:py-20 lg:pl-20 lg:pr-5">

               <div class="my-2"><b>Current Payment Method</b></div>

                <div class="shadow bg-white rounded">

                    <table class="w-full border-spacing-3 border-separate text-sm">
                        <tr>
                            <th>NUMBER</th>
                            <th></th>
                        </tr>
                          
                            <tr data-pm="{{ Auth::user()->id }}">
                                <td><img class="inline-block mr-1" src="{{ Storage::disk('public')->url('image/'.Auth::user()->pm_type.'.svg') }}"> **** **** **** {{ Auth::user()->pm_last_four }}@if(isset($default) && Auth::user()->id == $default->id) <span class="bg-green-400 rounded text-white px-2 py-1 ml-2 text-xs"><span class="icon-star-full mr-1"></span>Default</span> @endif</td>
                            </tr>
                   
                    </table>

                </div>

                <x-errors class="mt-2" title="We found {errors} error(s)"/>
                <x-button type="submit" class="w-full main-bg-c mt-2" spinner primary label="Change Plan" />
                <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">
           </div>

    </form>   

</main>

@endsection