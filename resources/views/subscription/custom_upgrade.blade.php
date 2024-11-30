@extends('layouts.payment')

@section('title')
Upgrade Subscription
@endsection

@section('main')


<script src="https://www.paypal.com/sdk/js?client-id={{env('PAYPAL_CLIENT_ID')}}&vault=true&intent=subscription"></script>
<script src="https://js.stripe.com/v3/"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('css/billing.css?'.time().'') }}" rel="stylesheet">

	<main class="grid m-auto h-full max-w-5xl grid-cols-1 md:grid-cols-2">
		


		<div class="shopping-cart-section pb-32 p-7 lg:p-20">
		    
		    <div class="mb-2 hidden md:block">
		        <!--          <div class="border border-gray-200 rounded-full inline-block"> -->
		        <x-application-mark class="h-9 w-auto" />
		        <!--          </div> -->

		    </div>
		    <x-errors title="Warning there's an error"/>

		    <div class="full-cart-price">
		        <h2 class="font-bold text-4xl text-black">${{$plan['actual_cost']}}</h2>
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
		                        <div class="dropdown-name">${{$plan['actual_cost']}}</div>
		                        <div></div>
		                    </div>
		                </div>
		                <div class="flex text-sm items-center justify-end">
		                    <div><b>${{$plan['actual_cost']}}</b></div>
		                </div>
		            </div>

		        </div>

		        <div class="flex font-bold py-2">
		           <div class="w-full">Total Due</div>
		           <div class="text-right w-full total-cost">${{$plan['actual_cost']}}</div>
		        </div>

		    </div>

		    <div class="mt-4 text-right">
		        <a href="{{ url('terms-condition') }}" class="text-xs text-gray-400 pl-5 transition">Terms</a>
		        <a href="{{ url('privacy-policy') }}" class="text-xs text-gray-400 pl-5 transition">Privacy</a>
		    </div>

		</div>


        <!-- If payment method exist -->
	    <form class="order-first md:order-last" action="{{ url('stripe-payment-subscription-custom') }}" method="POST" id="addPaymentFrm" style="box-shadow: -6px 0 16px -9px rgb(0 0 0 / 10%);">
	        @csrf

	        <div class="md:hidden block bg-gray-100 py-2.5 px-4">
	            <!--          <div class="border border-gray-200 rounded-full inline-block"> -->
	            <x-application-mark class="block h-9 w-auto" />
	            <!--          </div> -->
	            <h2 class="font-bold text-4xl text-black">${{$plan['actual_cost']}}</h2>
	        </div>

	        <div class="md:hidden block bg-gray-100 border-t py-2.5 px-4">
	            <h2 class="font-bold text-2xl text-black">Payment</h2>
	        </div>

	       <div class="right-section h-full md:h-screen p-7 lg:py-20 lg:pl-20 lg:pr-5">

	           @livewire('shopping.checkout.shipping-address')
	           <div class="my-2"><b>Payment Method</b></div>

	           <div><div id="paypal-button-container"></div></div>

	           <div class="flex">
	                <div class="w-full bg-gray-200 rounded-t w-full border-0 text-center h-8">
	                   <div class="flex justify-center leading-8">
	                      <div class="flex top-2.5 right-10">
	                          <div class="card-span pr-1 font-bold">Credit Card</div>
	                <!--           <img src="{{ Storage::disk('public')->url('image/visa.svg') }}">
	                          <img class="ml-1" src="{{ Storage::disk('public')->url('image/mastercard.svg') }}">
	                          <img class="ml-1" src="{{ Storage::disk('public')->url('image/ae.svg') }}"> -->
	                     </div>
	                   </div>
	                </div>
	           </div>  

	             <div class="border p-2 rounded-b">
	               <x-stripe-card-info-input></x-stripe-card-info-input>
	                @livewire('shopping.checkout.billing-address',['billing'=>'custom','type'=>'custom_only'])
	            </div>

	                <!-- @if(isset($comment))
	                <div>
	                   <textarea class="block w-full outline-none p-2 rounded-md text-xs mb-3 h-20 resize-none" name="comment" placeholder="Add Comment"></textarea>
	                </div>
	                @endif -->
	                <x-errors class="mt-2" title="We found {errors} error(s)"/>
	                <x-button type="submit" class="w-full main-bg-c mt-2" spinner primary label="Confirm & Pay" />

	       </div>

	    </form>


	</main>


<script type="text/javascript">

var stripe = Stripe("{{ env('STRIPE_KEY') }}");
var data = {'plan_id':'{{ $req->plan_id }}'};
var country = "{{ url('storage/json/country.json') }}";

</script>
<script type="text/javascript" src="{{ asset('js/billing.js') }}"></script>
<script type="text/javascript">

		paypal.Buttons({
		    locale: 'en_US',
		    style: {
		        height: 31,
		        label:'pay',
		        color: 'blue',
		        layout: 'horizontal',
	    		tagline: 'false',
		    },

	   		createSubscription: function(data, actions) {
	          return actions.subscription.create({
	            plan_id: '{{ $plan["paypal_plan_id"] }}'
	          });
	        },

	        onApprove: function(data, actions) {
	        	console.log(actions);
	            console.log(data);
	           $.ajax({
	                url: "{{ url('paypal-payment-subscription') }}",
	                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
	                method: "POST",
	                data: {sub_id:data.subscriptionID,name:"{{ $plan['name'] }}"},
	                beforeSend: function () {
	                    processing_show();
	                },
	                success: function (t) {
	                	console.log(t);
	                	if (t.message == "Subscribed") {
	                		window.location.replace(window.location.origin+'/user/developer');
                            processing_hide();
	                	}
	                },
	                error: function (t, e, n) {
	                    processing_hide();
                        
                        window.$wireui.dialog({
	                        title: 'Action could not be performed',
	                        description:'There was an error trying to process the payment. Please contact us about this matter',
	                        icon:'error'
	                    });
	                },
	            });

	        }
      }).render('#paypal-button-container'); // Renders the PayPal button


</script>
@endsection