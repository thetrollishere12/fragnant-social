<div class="my-8">

    <!-- Monthly/Yearly Toggle -->
    @if($types->count() > 1)
    <div class="max-w-3xl mx-auto grid gap-2 grid-cols-2 rounded-lg text-center mb-6">
        @foreach($types as $t)
        <div wire:click="subscription_type('{{$t}}')" 
             class="rounded-lg py-2 border text-sm font-medium transition duration-300 @if($type == $t) main-bg-c text-white @else cursor-pointer bg-white main-t-c hover:bg-gray-100 @endif">
            {{ $t }}
        </div>
        @endforeach
    </div>
    @endif



    <!-- Subscription Boxes -->
    <div class="text-center grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($subscriptions as $subscription)


            <div class="flex">


                    @if($subscription->public == true)

                        <div class="subscription-box w-full py-6 px-4 shadow-md rounded-lg bg-white border border-gray-200 hover:shadow-lg transition duration-300 flex flex-col justify-between">
                            <!-- Subscription Icon -->
                            <div class="w-24 mx-auto mb-4">
                                <img class="w-full" src='{{ Storage::disk("public")->url("image/subscription/".$subscription["icon_image"]) }}'>
                            </div>

                            <!-- Subscription Name -->
                            <h2 class="font-bold text-xl py-2 uppercase text-gray-800 tracking-wide">{{ $subscription['name'] }}</h2>

                            <!-- Benefits -->
                            <div class="text-left h-auto py-4 border-t border-gray-200 mt-4 flex-grow">
                                <ul class="space-y-2 mt-3">
                                    @foreach($subscription["benefits"] as $benefit)
                                    <li class="flex items-start text-sm font-medium text-gray-700">
                                        <span class="icon-arrow-right text-indigo-500 mr-2 mt-1"></span>
                                        <span class="leading-tight @if(strpos($benefit, '+') !== false && (!$user_subbed || ($user_subbed && $user_subbed->type != $subscription['name']))) main-t-c @endif">{{ $benefit }}</span>
                                    </li>
                                    @endforeach

                                    <!-- Bandwidth Information -->
                                    @if($subscription["time_limit_amount"])
                                    <li class="flex items-start text-sm font-medium text-gray-700">
                                        <span class="icon-checkmark text-green-500 mr-2 mt-1"></span>
                                        {{ $subscription["time_limit_amount"] }} Seconds @if($subscription["recurring_type"]) / {{ $subscription["recurring_type"] }} @endif
                                    </li>
                                    @endif
                                </ul>
                            </div>

                            <!-- Pricing -->
                            <div class="py-4 mt-4">
                                @php
                                    $monthlyPrice = $type == 'Year' ? $subscription['price'] / 12 : $subscription['price'];
                                    $monthlySalePrice = $type == 'Year' ? $subscription['sale_price'] / 12 : $subscription['sale_price'];
                                @endphp

                                @if(isset($subscription['sale_price']))
                                <h3 class="font-bold text-3xl text-gray-400 line-through">${{ number_format($monthlyPrice, 2) }}/mo</h3>
                                <h3 class="font-bold text-4xl">${{ number_format($monthlySalePrice, 2) }}/mo</h3>
                                @else
                                <h3 class="font-bold text-4xl">
                                    @if($monthlyPrice <= 0) FREE @else ${{ number_format($monthlyPrice, 2) }}/mo @endif
                                </h3>
                                @endif

                                @if($subscription['recurring_type'])
                                <p class="text-xs text-gray-500 mt-1">Billed every {{ strtolower($subscription['recurring_type']) }}</p>
                                @endif
                            </div>

                            <!-- Call to Action -->
                            <div class="mt-6">
                                @guest
                                <a href="{{ url('register?link=subscription-api-pricing') }}">
                                    <button class="w-full py-2 rounded bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition duration-300">SIGN UP</button>
                                </a>
                                @else


                                    @if($subscription["price"] > 0 || $subscription["sale_price"] > 0)

                                        @if($user_subbed)

                                            @if($user_subbed->type == $subscription["name"] && in_array($user_subbed->type, $plan_name))
                                                <button class="w-full py-2 rounded bg-gray-400 text-gray-600 cursor-default">Current plan</button>
                                            @else
                                                @if(\App\Helper\SubscriptionHelper::subscribed(Auth::user()->id, $plan_name)->count() > 0 && $user_subbed->stripe_id)
                                                <a href="{{ url('subscription-change/'.$subscription['id']) }}">
                                                    <button class="w-full py-2 rounded bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition duration-300">Change plan</button>
                                                </a>
                                                @endif
                                            @endif

                                        @else
                                        <a href="{{ url('subscription-upgrade/'.$subscription['id']) }}">
                                            <button class="w-full py-2 rounded bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition duration-300">
                                                @if($subscription["trial_period_days"] && (!\App\Helper\SubscriptionHelper::has_used_free_trial(Auth::user()->id,$subscription['subscription_product_id'])))
                                                Start your free trial
                                                @else
                                                Get started
                                                @endif
                                            </button>
                                        </a>
                                        @endif
                                    @endif


                                @endguest
                            </div>
                        </div>


                    @endif

            </div>



        @endforeach
    </div>
</div>