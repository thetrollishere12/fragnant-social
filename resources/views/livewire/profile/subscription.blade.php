<div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://js.stripe.com/v3/"></script>

<form action="{{ url('user/subscription/update-payment-info') }}" method="POST" id="addPaymentFrm">

    <x-wui-modal.card title="Update Payment Info" blur wire:model.defer="paymentInfoModal">
        <div class="px-3">
            @csrf
            <div class="mb-1"><b>Card Information</b></div>
            <div id="paymentResponse" class="text-red-500 text-xs py-1"></div>

            <div class="credit-card-container rounded-md border border-gray-200 my-2">
                <div class="form-group card-number-container p-2.5 border-b border-gray-200 relative">
                    <div id="card_number" class="field"></div>
                    <div class="flex gap-1 absolute top-2.5 right-2">
                        <img src="{{ Storage::disk('public')->url('image/logo/visa.svg') }}">
                        <img src="{{ Storage::disk('public')->url('image/logo/mastercard.svg') }}">
                        <img src="{{ Storage::disk('public')->url('image/logo/amex.svg') }}">
                        <img src="{{ Storage::disk('public')->url('image/logo/discover.svg') }}">
                    </div>
                </div>
                <div class="card-container-ex-cvc grid grid-cols-2">
                    <div class="form-group expiry-date-container border-r border-gray-200 p-2.5">
                        <div id="card_expiry" class="field"></div>
                    </div>
                    <div class="form-group cvc-container p-2.5">
                        <div id="card_cvc" class="field"></div>
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                <x-button flat label="Cancel" x-on:click="close" />
                <x-button primary label="Save" type="submit" />
            </div>
        </x-slot>
    </x-wui-modal.card>

</form>

<script>
    var stripe = Stripe("{{ env('STRIPE_KEY') }}");
    var data = {};
</script>


<script src="{{ asset('js/billing.js') }}"></script>




@php

    $allSubscriptionsNull = collect($subscription_products)->every(function ($item) {
        return is_null($item['subscription']);
    });

    $allSubscriptionPlansNull = collect($subscription_products)->every(function ($item) {
        return is_null($item['subscription_plan']);
    });

@endphp

@if($allSubscriptionsNull && $allSubscriptionPlansNull)


    <div class="mx-auto">
                
        @foreach($products as $product)
            @livewire('subscription.pricing',['product_name'=>$product->name])
        @endforeach

    </div>


@else




@foreach($subscription_products as $key => $subscription)





    @if($subscription['details'])
    <div class="flex gap-1 my-2 items-center">
        <div class="font-bold p-2 text-2xl">Billing</div>

        @if(\App\Helper\SubscriptionHelper::subscribed(Auth::user()->id, $subscription['subscription_plan']["name"])->count() > 0)

            @if(Auth::user()->subscription($subscription['details']['name'])->onTrial())
                <div class="bg-indigo-500 text-white rounded-md text-xs px-3 py-2">Free Trial Until {{ \Carbon\Carbon::parse($subscription['subscription']['trial_ends_at'])->format('M d') }}</div>
            @elseif(Auth::user()->subscription($subscription['details']['name'])->hasExpiredTrial())

                <div class="bg-red-500 text-white rounded-md text-xs px-3 py-2">Your free trial ended on {{ \Carbon\Carbon::parse($subscription['subscription']['trial_ends_at'])->format('M d') }}</div>

            @endif

        @endif

    </div>

    <div>

        @if(\App\Helper\SubscriptionHelper::user_is_pastDue($subscription['subscription_product']['name'])->count() > 0)
            <div class="bg-red-100 shadow-md rounded-lg p-4 w-full my-2 mx-auto">
                <div><b>Past Due</b></div>
                <div class="pt-2">Your payment is past due. Please update your payment info to continue enjoying our services. Please contact us if you run into any issues!</div>
            </div>
        @endif

        @if(\App\Helper\SubscriptionHelper::user_is_onGracePeriod($subscription['subscription_product']['name'])->count() > 0)
        <div class="bg-green-100 shadow-md rounded-lg p-4 w-full my-2 mx-auto">
            <div><b>Subscription End Date</b></div>
            <div class="pt-2 pb-3">Your subscription is scheduled to end on <b>{{ Carbon\Carbon::parse($subscription['details']['period_end'])->format('M d Y') }}</b></div>
            <div>
                <x-button wire:click="resume_subscription('{{ $subscription['subscription_product']['name'] }}')" class="w-full" green spinner="resume_subscription" label="Resume Subscription" />
            </div>
        </div>
        @endif






        <div class="shadow rounded-lg m-2 p-2 text-sm w-full mx-auto bg-white">

        

            <div class="grid grid-cols-3 m-3 items-center">
                <div>Billing</div>
                <div>
                    @if(\App\Helper\SubscriptionHelper::user_is_onGracePeriod($subscription['subscription_product']['name'])->count() > 0 == false)
                        Next ${{$subscription['details']['amount']}} payment will be charged on {{ Carbon\Carbon::parse($subscription['details']['period_end'])->format('M d Y') }}
                    @elseif(\App\Helper\SubscriptionHelper::user_is_onGracePeriod($subscription['subscription_product']['name'])->count() > 0 == true)
                        Billing scheduled to end on {{ Carbon\Carbon::parse($subscription['details']['period_end'])->format('M d') }}
                    @else
                        No Billing
                    @endif
                </div>
                <div></div>
            </div>

            <div class="grid grid-cols-3 m-3 items-center">
                <div>Valid</div>
                <div>
                    @if(isset($subscription['details']['period_valid']))
                    Valid through {{ Carbon\Carbon::parse($subscription['details']['period_valid'])->format('M d Y') }} to {{ Carbon\Carbon::parse($subscription['details']['period_end'])->format('M d Y') }}
                    @else
                    Currently None
                    @endif
                </div>
                <div>
                    

        


                </div>
            </div>

            <div class="grid grid-cols-3 m-3 items-center">
                <div>Payment Information</div>
                @if($subscription['subscription']['payment_method'] == 'Stripe')
                    <div><img class="card_img inline-block mr-1" src="{{asset('storage/image/logo/'.strtolower(Auth::user()['pm_type']).'.svg')}}"> ending with {{ Auth::user()['pm_last_four'] }}</div>
                    <div><x-button wire:click="update_payment_info" class="main-bg-c text-white rounded-md text-xs px-3 py-2" spinner="update_payment_info" label="Update Payment Info" /></div>
                @elseif($subscription['subscription']['payment_method'] == 'Paypal' && isset(Auth::user()['paypal_id']) && isset(Auth::user()['paypal_email']))
                    <div><img class="card_img inline-block mr-1" src="{{asset('storage/image/logo/paypal.svg')}}"> {{ Auth::user()['paypal_email'] }}</div>
                    <div></div>
                @else
                    <div>No Payment Info</div>
                    <div></div>
                @endif
            </div>

        </div>



    

    </div>

    @endif





    @if($subscription['subscription_plan'])

    <div class="font-bold p-2 text-2xl">Subscription</div>

    <div class="shadow-md bg-white rounded-lg m-2 w-full mx-auto">

        <div class="rounded-t-lg capitalize main-bg-c text-white text-lg px-8 py-3">{{ $subscription['subscription_plan']["name"] }}</div>

        <div class="p-4">
            @foreach($subscription['subscription_plan']["benefits"] as $benefit)
            <div class="capitalize my-1"><span class="icon-checkmark text-green-400 text-xs mx-2"></span>{{ $benefit }}</div>
            @endforeach

            @if($subscription['subscription_plan']["time_limit_amount"])
            <div class="capitalize my-1"><span class="icon-checkmark text-green-400 text-xs mx-2"></span>{{ $subscription['subscription_plan']["time_limit_amount"] }} Seconds
            @if($subscription['subscription_plan']["recurring_type"])/{{ $subscription['subscription_plan']["recurring_type"] }}@endif</div>
            @endif





            
        </div>


        @if($subscription['subscription'])
        <div id="subscription-footer" class="p-8 pt-0">



                @if(\App\Helper\SubscriptionHelper::user_is_onGracePeriod($subscription['subscription_product']['name'])->count() > 0)
                <x-button wire:click="resume_subscription('{{ $subscription['subscription_product']['name'] }}')" class="w-full text-white" green spinner="resume_subscription" label="Resume Subscription" />
                @elseif(\App\Helper\SubscriptionHelper::user_is_onGracePeriod($subscription['subscription_product']['name'])->count() == 0)
                <x-button wire:click="cancel_subscription('{{ $subscription['subscription_product']['name'] }}')" class="mt-2 w-full" spinner="cancel_subscription" light negative label="Cancel Subscription" />
                @endif


        </div>
        @endif
        

    </div>

    @endif




@endforeach






<div class="font-bold p-2 text-2xl">Details</div>


<div class="grid md:grid-cols-2 gap-3 mt-2">
 


    <!-- Storage Usage -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Storage Usage</h2>
        <div class="flex items-center justify-between mb-4">
            <div class="text-left">
                <p class="text-sm font-medium text-gray-500">Used</p>
                <p class="text-2xl font-bold text-gray-800">{{ $storage_data['currentStorage'] }} GB</p>
            </div>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-500">Max Allowed</p>
                <p class="text-2xl font-bold text-gray-800">{{ $storage_data['maxStorage'] ?: 0 }} GB</p>
            </div>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5 relative">
            <div 
                class="{{ $storage_data['currentStorage'] > $storage_data['maxStorage'] ? 'bg-red-500' : 'main-bg-c' }} h-2.5 rounded-full" 
                style="width: {{ $storage_data['maxStorage'] > 0 ? min(($storage_data['currentStorage'] / $storage_data['maxStorage']) * 100, 100) : 0 }}%">
            </div>
        </div>
        <p class="text-sm mt-2 text-gray-500 text-right">
            {{ $storage_data['maxStorage'] > 0 ? round(($storage_data['currentStorage'] / $storage_data['maxStorage']) * 100, 2) : 0 }}% used
        </p>
        @if ($storage_data['currentStorage'] > $storage_data['maxStorage'])
            <p class="text-sm mt-2 text-red-500 font-bold text-right">You have surpassed your storage limit!</p>
        @endif
    </div>

    <!-- Monthly Video Uploads -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Monthly Video Uploads</h2>
        <div class="flex items-center justify-between mb-4">
            <div class="text-left">
                <p class="text-sm font-medium text-gray-500">Uploaded</p>
                <p class="text-2xl font-bold text-gray-800">{{ $video_data['currentVideoCount'] }}</p>
            </div>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-500">Max Allowed</p>
                <p class="text-2xl font-bold text-gray-800">{{ $video_data['maxVideoLimit'] ?: 0 }}</p>
            </div>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5 relative">
            <div 
                class="{{ $video_data['currentVideoCount'] > $video_data['maxVideoLimit'] ? 'bg-red-500' : 'bg-green-500' }} h-2.5 rounded-full" 
                style="width: {{ $video_data['maxVideoLimit'] > 0 ? min(($video_data['currentVideoCount'] / $video_data['maxVideoLimit']) * 100, 100) : 0 }}%">
            </div>
        </div>
        <p class="text-sm mt-2 text-gray-500 text-right">
            {{ $video_data['maxVideoLimit'] > 0 ? round(($video_data['currentVideoCount'] / $video_data['maxVideoLimit']) * 100, 2) : 0 }}% used
        </p>
        @if ($video_data['currentVideoCount'] > $video_data['maxVideoLimit'])
            <p class="text-sm mt-2 text-red-500 font-bold text-right">You have surpassed your video upload limit!</p>
        @endif
    </div>



   <div class="bg-white rounded-lg p-2 shadow">
        <canvas class="w-full" id="countChart"></canvas>
    </div>




</div>

    
    <script>

document.addEventListener('livewire:initialized', function () {
    console.log("Livewire loaded successfully.");

    let countCtx = document.getElementById('countChart').getContext('2d');

    let chartDataValues = @json($chartData['video_count']['values']).map(Number);


    let countChartData = {
        labels: @json($chartData['labels']),
        datasets: [
            {
                label: @json($chartData['video_count']['name']),
                backgroundColor: '#6366f1',
                data: chartDataValues,
            },
        ],
    };

    let maxCountValue = Math.max(...countChartData.datasets[0].data);

    let countChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    font: {
                        size: 10,
                    },
                },
            },
            y: {
                grid: {},
                max: maxCountValue + 5,
                ticks: {
                    beginAtZero: true,
                    precision: 0,
                    stepSize: 1,
                    font: {
                        size: 10,
                    },
                },
            },
        },
    };

    new Chart(countCtx, {
        type: 'bar',
        data: countChartData,
        options: countChartOptions,
    });
});

</script>





@endif



    

</div>