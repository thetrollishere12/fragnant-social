<div class="my-3">
    <!-- Monthly/Yearly Toggle -->
    <div class="max-w-3xl mx-auto grid gap-1 grid-cols-2 rounded text-center mb-2">

        @foreach($types as $t)
        <div wire:click="subscription_type('{{$t}}')" class="rounded py-2 border @if($type == $t) main-bg-c text-white @else cursor-pointer bg-white main-t-c @endif">{{ $t }}</div>
        @endforeach

    </div>

    <!-- Subscription Boxes -->
    <div class="text-center grid grid-cols-1 md:grid-cols-3 gap-2">
        @foreach($subscriptions as $subscription)

        @if($subscription->public == true)
        <div class="subscription-box w-full py-12 px-3 shadow rounded bg-white" style="
                @if($user_subbed)
                    @if($user_subbed->name == $subscription['name'])
                        color: white;background-color: #6366f1 !important;
                    @endif
                @endif

            ">
            
            <div class="w-20 mx-auto">
                <img class="w-full" src='{{ Storage::disk("public")->url("image/subscription/".$subscription["icon_image"]) }}'>
            </div>

            <h2 class="font-bold text-xl py-2 uppercase">{{ $subscription['name'] }}</h2>

            <!-- Benefits -->
            <div class="text-sm h-44 flex items-center justify-center">
                <div class="leading-relaxed">
                    @foreach($subscription["benefits"] as $benefit)
                        <div class="capitalize @if(strpos($benefit, '+') !== false && (!$user_subbed || ($user_subbed && $user_subbed->name != $subscription['name']))) main-t-c @endif">{{ $benefit }}</div>
                    @endforeach

                    <!-- Bandwidth Information -->
                    @if($subscription["time_limit_amount"])
                    <div class="capitalize my-1"><span class="icon-checkmark text-green-400 text-xs mx-2"></span>{{ $subscription["time_limit_amount"] }} Seconds
                    @if($subscription["recurring_type"])/ {{ $subscription["recurring_type"] }}@endif</div>
                    @endif

                </div>
            </div>




            <div class="flex flex-col items-center">
                @php
                    $monthlyPrice = $type == 'Year' ? $subscription['price'] / 12 : $subscription['price'];
                    $monthlySalePrice = $type == 'Year' ? $subscription['sale_price'] / 12 : $subscription['sale_price'];
                @endphp

                @if(isset($subscription['sale_price']))
                    <h3 class="font-bold text-2xl opacity-70 line-through">${{ number_format($monthlyPrice, 2) }}/mo</h3>
                    <h3 class="font-bold text-3xl">${{ number_format($monthlySalePrice, 2) }}/mo</h3>
                @else
                    <h3 class="font-bold text-3xl">@if($monthlyPrice <= 0) FREE @else ${{ number_format($monthlyPrice, 2) }}/mo @endif</h3>
                @endif
            </div>




            @guest
            <a href="{{ url('register?link=subscription-api-pricing') }}"><button class="main-bg-c">SIGN UP</button></a>

            @else








                @if($subscription["price"] > 0 || $subscription["sale_price"] > 0)

                        <!-- Already Subbed -->
                        @if($user_subbed)

                            <!-- If subbed show which one its on -->
                            @if($user_subbed->name == $subscription["name"] && in_array($user_subbed->name, $plan_name))
                                <button class="cursor-default">CURRENT PLAN</button>
                            @else

                                @if(\App\Helper\SubscriptionHelper::user_is_subscribed_to($plan_name)->count() > 0 && $user_subbed->stripe_id)
                                <form method="POST" action="{{ url('subscription-change') }}">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $subscription['id'] }}">
                                    <button class="main-bg-c" type="submit">CHANGE PLAN</button>
                                </form>
                                @endif
                                
                            @endif

                        <!-- Not subbed -->
                        @else
                                <form method="POST" action="{{ url('subscription-upgrade') }}">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $subscription['id'] }}">
                                    <button class="main-bg-c" type="submit">GET STARTED</button>
                                </form>
                        @endif
                @endif










            @endguest

        </div>
        @endif
        @endforeach
    </div>
</div>