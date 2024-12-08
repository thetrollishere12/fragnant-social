

<div>

<script src="https://www.paypal.com/sdk/js?client-id={{env('PAYPAL_CLIENT_ID')}}&vault=true&intent=subscription"></script>
<script src="https://js.stripe.com/v3/"></script>




<form id="addPaymentFrm">
        @csrf
        <div class="md:hidden block bg-gray-100 py-2.5 px-4">
            <x-application-mark class="block h-9 w-auto" />
            <h2 class="font-bold text-4xl text-black">${{$plan['sale_price'] ? $plan['sale_price'] : $plan['price']}}/{{$plan['recurring_type']}}</h2>
        </div>
        <div class="md:hidden block bg-gray-100 border-t py-2.5 px-4">
            <h2 class="font-bold text-2xl text-black">Payment</h2>
        </div>
        <div class="right-section h-full md:h-screen p-7 lg:py-20 lg:pl-20 lg:pr-5">
            <div class="my-2"><b>Payment Method</b></div>
            <div wire:ignore><div id="paypal-button-container"></div></div>
            <div class="flex">
                <div class="w-full bg-gray-200 rounded-t w-full border-0 text-center h-8">
                    <div class="flex justify-center leading-8">
                        <div class="flex top-2.5 right-10">
                            <div class="card-span pr-1 font-bold">Credit Card</div>
                        </div>
                    </div>
                </div>
            </div>  
            <div class="border pt-2 px-2 rounded-b">
                


                <div id="payment-element">
        <!-- Elements will create form elements here -->
      </div>





    <button id="submit" class="w-full main-bg-c my-2 p-2 text-white rounded">
        <div class="spinner hidden" id="spinner"></div>
        <span id="button-text">Pay & Subscribe</span>
      </button>
      <div id="payment-message" class="hidden"></div>








            </div>
            
        </div>
    </form>



<style type="text/css">



#payment-message {
  color: rgb(105, 115, 134);
  font-size: 16px;
  line-height: 20px;
  padding-top: 12px;
  text-align: center;
}

/* Buttons and links */
#addPaymentFrm button {
  background: #e5e7eb;
  font-family: Arial, sans-serif;
  color: #ffffff;
  border-radius: 4px;
  border: 0;
  padding: 12px 16px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: block;
  transition: all 0.2s ease;
  box-shadow: 0px 4px 5.5px 0px rgba(0, 0, 0, 0.07);
  width: 100%;
}
#addPaymentFrm button:hover {
  filter: contrast(115%);
}
#addPaymentFrm button:disabled {
  opacity: 0.5;
  cursor: default;
}

/* spinner/processing state, errors */
.spinner,
.spinner:before,
.spinner:after {
  border-radius: 50%;
}
.spinner {
  color: #ffffff;
  font-size: 22px;
  text-indent: -99999px;
  margin: 0px auto;
  position: relative;
  width: 20px;
  height: 20px;
  box-shadow: inset 0 0 0 2px;
  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);
}
.spinner:before,
.spinner:after {
  position: absolute;
  content: "";
}
.spinner:before {
  width: 10.4px;
  height: 20.4px;
  background: #e5e7eb;
  border-radius: 20.4px 0 0 20.4px;
  top: -0.2px;
  left: -0.2px;
  -webkit-transform-origin: 10.4px 10.2px;
  transform-origin: 10.4px 10.2px;
  -webkit-animation: loading 2s infinite ease 1.5s;
  animation: loading 2s infinite ease 1.5s;
}
.spinner:after {
  width: 10.4px;
  height: 10.2px;
  background: #e5e7eb;
  border-radius: 0 10.2px 10.2px 0;
  top: -0.1px;
  left: 10.2px;
  -webkit-transform-origin: 0px 10.2px;
  transform-origin: 0px 10.2px;
  -webkit-animation: loading 2s infinite ease;
  animation: loading 2s infinite ease;
}

@-webkit-keyframes loading {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@keyframes loading {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}




</style>


<script type="text/javascript">

var stripe = Stripe("{{ env('STRIPE_KEY') }}");
var data = {'plan_id':'price_1P5B7uL0y3BaIHedBpvb6pda'};
var country = "{{ url('storage/json/country.json') }}";

</script>
<script type="text/javascript">
    



let elements;

initialize();

document
    .querySelector("#addPaymentFrm")
    .addEventListener("submit", handleSubmit);

// Fetches a payment intent and captures the client secret
async function initialize() {

    elements = stripe.elements({clientSecret: '{{ $payment_intent }}'});

    const paymentElementOptions = {
      layout: "accordion",
    };

    const paymentElement = elements.create("payment", paymentElementOptions);

    paymentElement.mount("#payment-element");

}



async function handleSubmit(e) {

    e.preventDefault();
    setLoading(true);


  const {error} = await stripe.confirmPayment({
    //`Elements` instance that was used to create the Payment Element
    elements,
    confirmParams: {
      return_url: "{{ url('stripe-payment-subscription/'.$plan['id']) }}",
    }
  });

    // This point will only be reached if there is an immediate error when
    // confirming the payment. Otherwise, your customer will be redirected to
    // your `return_url`. For some payment methods like iDEAL, your customer will
    // be redirected to an intermediate site first to authorize the payment, then
    // redirected to the `return_url`.
    if (error.type === "card_error" || error.type === "validation_error") {
        showMessage(error.message);
    } else {
        showMessage("An unexpected error occurred.");
    }

    setLoading(false);
}

// Fetches the payment intent status after payment submission
async function checkStatus() {
    const clientSecret = new URLSearchParams(window.location.search).get(
        "payment_intent_client_secret"
    );

    if (!clientSecret) {
        return;
    }

    const {
        paymentIntent
    } = await stripe.retrievePaymentIntent(clientSecret);

    switch (paymentIntent.status) {
        case "succeeded":
            showMessage("Payment succeeded!");
            break;
        case "processing":
            showMessage("Your payment is processing.");
            break;
        case "requires_payment_method":
            showMessage("Your payment was not successful, please try again.");
            break;
        default:
            showMessage("Something went wrong.");
            break;
    }
}

// ------- UI helpers -------

function showMessage(messageText) {
    const messageContainer = document.querySelector("#payment-message");

    messageContainer.classList.remove("hidden");
    messageContainer.textContent = messageText;

    setTimeout(function() {
        messageContainer.classList.add("hidden");
        messageContainer.textContent = "";
    }, 4000);
}

// Show a spinner on payment submission
function setLoading(isLoading) {
    if (isLoading) {
    // Disable the button and show a spinner
    document.querySelector("#submit").disabled = true;
    document.querySelector("#spinner").classList.remove("hidden");
    document.querySelector("#button-text").classList.add("hidden");
  } else {
    document.querySelector("#submit").disabled = false;
    document.querySelector("#spinner").classList.add("hidden");
    document.querySelector("#button-text").classList.remove("hidden");
  }
}

function processing_show(){
    $('#processing-modal').modal('show');
}

function processing_hide(){
    $('#processing-modal').modal('hide');
}

$("form[name=paypal-thank-form]").submit(function(e){
    e.preventDefault();
});

</script>
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



</div>