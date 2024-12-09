
<div>




<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://js.stripe.com/v3/"></script>


<form action="{{ url('user/developer/update-payment-info') }}" method="POST" id="addPaymentFrm">

<x-wui-modal.card title="Update Payment Info" blur wire:model.defer="paymentInfoModal">
    
    <div class="px-3">
        


            

            @csrf



                <div class="mb-1"><b>Card Information</b></div>
                <div id="paymentResponse" class="text-red-500 text-xs py-1"></div>
                <div class="credit-card-container rounded-md border border-gray-200 my-2">
                  <div class="form-group card-number-container p-2.5 border-b border-gray-200 relative">
                     <div id="card_number" class="field"></div>
                     <div class="flex gap-1 absolute top-2.5 right-2">
                          <img src="{{ Storage::disk('public')->url('image/visa.svg') }}">
                          <img src="{{ Storage::disk('public')->url('image/mastercard.svg') }}">
                          <img src="{{ Storage::disk('public')->url('image/amex.svg') }}">
                          <img src="{{ Storage::disk('public')->url('image/discover.svg') }}">
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
            <div class="flex">
                
                <x-button primary label="Save" type="submit"/>
            </div>
        </div>
    </x-slot>
    
</x-wui-modal.card>
</form>



    <script type="text/javascript">
        var stripe = Stripe("{{ env('STRIPE_KEY') }}");
        var data = {};
    </script>

    <script type="text/javascript" src="{{ asset('js/billing.js') }}"></script>




<div class="grid md:grid-cols-2 gap-3 mt-3">
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


    @foreach($subscriptions as $key => $subscription)



    @if($subscription['details'])
    <div class="font-bold p-2 my-2 text-2xl">Subscription</div>

    <div>
        
        @if(\App\Helper\SubscriptionHelper::user_is_pastDue($key)->count() > 0)
            <div class="bg-red-100 rounded-lg p-4 w-full my-2 mx-auto">
                <div><b>Past Due</b></div>
                <div class="pt-2">Your payment is past due. Please update your payment info to continue enjoying our services. Please contact us if you run into any issues!</div>
            </div>
        @endif

        @if(\App\Helper\SubscriptionHelper::user_is_onGracePeriod($key)->count() > 0)
        <div class="bg-green-100 rounded-lg p-4 w-full my-2 mx-auto">
            <div><b>Subscription End Date</b></div>
            <div class="pt-2 pb-3">Your subscription is scheduled to end on <b>{{ Carbon\Carbon::parse($subscription['period_end'])->format('M d Y') }}</b></div>
            <div>    
                <x-button wire:click="resume_subscription('{{ $key }}')" class="w-full" green spinner="resume_subscription" label="Resume Subscription" />
            </div>
        </div>
        @endif

        @if(\App\Helper\SubscriptionHelper::user_is_subscribed_to($subscription['details']["name"])->count() > 0)
        <div class="shadow rounded-lg m-2 p-2 text-sm w-full mx-auto bg-white">

            <div class="grid grid-cols-3 m-3 items-center">
                
                <div>Billing</div>
                <div>
                    @if(\App\Helper\SubscriptionHelper::user_is_onGracePeriod($key)->count() > 0 == false)
                        Next ${{$subscription['amount']}} payment will be charged on {{ Carbon\Carbon::parse($subscription['period_end'])->format('M d Y') }}
                    @elseif(\App\Helper\SubscriptionHelper::user_is_onGracePeriod($key)->count() > 0 == true)
                        Billing scheduled to end on {{ Carbon\Carbon::parse($subscription['period_end'])->format('M d') }}
                    @else
                        No Billing
                    @endif
                </div>
                <div></div>

            </div>

            <div class="grid grid-cols-3 m-3 items-center">
                
                <div>Valid</div>
                <div>
                    @if(isset($subscription['period_valid']))
                    Valid throught {{ Carbon\Carbon::parse($subscription['period_valid'])->format('M d Y') }} to {{ Carbon\Carbon::parse($subscription['period_end'])->format('M d Y') }}
                    @else
                    Currently None
                    @endif
                </div>
                <div></div>

            </div>

            <div class="grid grid-cols-3 m-3 items-center">

                <div>Payment Information</div>
                @if($subscription['subscription']['payment_method'] == 'Stripe')
                    <div><img class="card_img inline-block mr-1" src="{{asset('storage/image/'.strtolower($subscription['user']['pm_type']).'.svg')}}"> ending with {{ $subscription['user']['pm_last_four'] }}</div>
                    <div><x-button wire:click="update_payment_info" class="main-bg-c text-white rounded-md text-xs px-3 py-2" spinner="update_payment_info" label="Update Payment Info" /></div>
                @elseif($subscription['subscription']['payment_method'] == 'Paypal' && isset($subscription['user']['paypal_id']) && isset($subscription['user']['paypal_email']))
                    <div><img class="card_img inline-block mr-1" src="{{asset('storage/image/paypal.svg')}}"> {{ $subscription['user']['paypal_email'] }}</div>
                    <div></div>
                @else
                    <div>No Payment Info</div>
                    <div></div>
                @endif

            </div>

        </div>
        @endif

        <div></div>

        <div class="shadow bg-white rounded-lg m-2 w-full mx-auto">

            <div class="rounded-t-lg capitalize main-bg-c text-white text-lg px-8 py-3">{{ $subscription['details']["name"] }}</div>

            <div class="p-4">
                
                @foreach($subscription['details']["benefits"] as $benefit)

                <div class="capitalize my-1"><span class="icon-checkmark text-green-400 text-xs mx-2"></span>{{ $benefit }}</div>

                @endforeach

                @if($subscription['details']["time_limit_amount"])
                <div class="capitalize my-1"><span class="icon-checkmark text-green-400 text-xs mx-2"></span>{{ $subscription['details']["time_limit_amount"] }} Seconds
                @if($subscription['details']["recurring_type"])/{{ $subscription['details']["recurring_type"] }}@endif</div>
                @endif

            </div>
          
            <div id="subscription-footer" class="p-8 pt-0">

                @if(\App\Helper\SubscriptionHelper::user_is_subscribed_to($subscription['details']["name"])->count() > 0)

                    @if(\App\Helper\SubscriptionHelper::user_is_onGracePeriod($key)->count() > 0)
                    <x-button wire:click="resume_subscription('{{ $key }}')" class="w-full text-white" green spinner="resume_subscription" label="Resume Subscription" />
                    @elseif(\App\Helper\SubscriptionHelper::user_is_onGracePeriod($key)->count() == 0)
                    <x-button wire:click="cancel_subscription('{{ $key }}')" class="mt-2 w-full" spinner="cancel_subscription" negative label="Cancel Subscription" />

                    @endif

                @else
                        <a href="{{ asset('subscription-pricing') }}"><button class="w-full main-bg-c text-sm text px-4 py-2 text-white rounded">Upgrade Plan</button></a>
                @endif

            </div>

        </div>

    </div>

    @endif

    @endforeach

</div>