<?php

namespace App\Livewire\Profile;

use Livewire\Component;

use Auth;
use WireUi\Traits\WireUiActions;
use Storage;
use Stripe\Stripe;
use Carbon\Carbon;

use Livewire\WithChartJS;
use Orchid\Screen\Fields\Chart as OrchidChart;


use App\Helper\SubscriptionHelper;
use App\Helper\AppHelper;

use App\Helper\PaypalHelper;

use App\Models\SubscriptionProduct;
use App\Models\SubscriptionPlan;
use App\Models\PublishedMedia;

use App\Models\DigitalAsset;



class Subscription extends Component
{



    public $subscription_details = [];
    public $details;


    public $subscriptions = [];
    public $paymentInfoModal = false;




    
    use WireUiActions;

    protected $listeners = ['refreshComponent' => '$refresh'];


    public function mount(){


        foreach (SubscriptionProduct::all() as $key => $subscription_product) {

            try{

                $subscription = SubscriptionHelper::user_is_subscribed_type($subscription_product->name);
            

                if ($subscription->count() > 0) {

                    switch ($subscription->first()->payment_method) {
                        case 'Stripe':

                            Stripe::setApiKey(config('services.stripe.secret'));

                            $stripe_id = $subscription->first()->stripe_id;

                            $account = \Stripe\Subscription::retrieve($stripe_id);

                            $this->subscriptions[$subscription_product->name] = [
                                'details' => SubscriptionHelper::subscription_details($subscription->first()->type, $subscription_product->name),
                                'payment_method' => 'Stripe',
                                'subscription' => $subscription->first(),
                                'user' => auth()->user(),
                                'name' => $subscription->first()->type,
                                'period_end' => $account->current_period_end,
                                'period_valid' => $account->current_period_start,
                                'amount' => number_format($account->plan->amount / 100, 2),
                                'active' => $account->plan->active,
                                'cancel' => $account->cancel_at_period_end,
                            ];

                        break;
                        case 'Paypal':

                            $bearer_token = PaypalHelper::paypal_bearer_token();

                            $subscription_details = PaypalHelper::paypal_subscription($subscription->first()->paypal_id,$bearer_token);

                            $this->subscriptions[$subscription_product->name] = [
                                'details' => SubscriptionHelper::subscription_details($subscription->first()->type, $subscription_product->name),
                                'payment_method' => 'Paypal',
                                'subscription' => $subscription->first(),
                                'user' => auth()->user(),
                                'name' => $subscription->first()->type,
                                'period_end' => Carbon::parse($subscription_details->billing_info->last_payment->time)->addDays(30),
                                'period_valid' => $subscription_details->billing_info->last_payment->time,
                                'amount' => number_format($subscription_details->billing_info->last_payment->amount->value, 2),
                                'active' => $subscription_details->status,
                                'cancel' => $subscription_details->status,
                            ];

                        break;
                        default:
                        break;
                    }

                }else{

                    $this->subscriptions[$subscription_product->name] = [
                        'details' => SubscriptionHelper::subscription_details('personal',$subscription_product->name)
                    ];

                }

            }catch(\Exception $e){
                $this->subscriptions[$subscription_product->name] = [
                    'details' => null
                ];
            }

        }

    }



    public function cancel_subscription($type){

        SubscriptionHelper::cancel_user_subscription($type);

        $this->notification()->send([
            'title'       => 'Subscription was cancelled',
            'description' => 'Your subscription was successfully cancelled',
            'icon'        => 'error',
        ]);

    }

    public function resume_subscription($type){

        SubscriptionHelper::resume_user_subscription($type);

        $this->notification()->send([
            'title'       => 'Subscription was resumed',
            'description' => 'Your subscription was successfully resumed',
            'icon'        => 'success',
        ]);

    }

    public function update_payment_info(){
        $this->paymentInfoModal = true;
    }


    public function render()
{
    // Define the start and end dates for the current month
    $start = Carbon::now()->startOfMonth();
    $end = Carbon::now()->endOfMonth();

    $digitalAssets = DigitalAsset::where('user_id', Auth::user()->id)->pluck('id');

    // Query to get the count of generated stories for each day within the period
    $publishedMedia = PublishedMedia::whereIn('digital_asset_id', $digitalAssets)
        ->whereBetween('created_at', [$start, $end])
        ->selectRaw('DATE(created_at) as date, COUNT(*) as video_count')
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();

    // Initialize arrays with all days of the current month
    $period = new \DatePeriod($start, \DateInterval::createFromDateString('1 day'), $end->addDay());
    $dates = [];
    foreach ($period as $date) {
        $dates[$date->format('M d')] = ['video_count' => 0];
    }

    // Fill in the count for the days that have generated stories
    foreach ($publishedMedia as $media) {
        $formattedDate = Carbon::parse($media->date)->format('M d');
        $dates[$formattedDate] = [
            'video_count' => $media->video_count,
        ];
    }

    // Prepare the chart data
    $chartData = [
        'labels' => array_keys($dates),
        'video_count' => [
            'name' => 'Generated Content',
            'values' => array_column($dates, 'video_count'),
        ],
    ];








    // Fetch user data
    $currentStorage = SubscriptionHelper::getCurrentStorageUsed(Auth::user()->id); // Bytes
    $maxStorage = SubscriptionHelper::getMaxStorageLimit(Auth::user()->id); // Bytes

    $storage_data = [
        'currentStorage'=>round($currentStorage/1024 / 1024 / 1024,2),
        'maxStorage'=>$maxStorage/1024 / 1024 / 1024
    ];

    // Fetch user video data
    $currentVideoCount = SubscriptionHelper::getMonthlyVideoCountByDate(Auth::user()->id);
    $maxVideoLimit = SubscriptionHelper::getMaxMonthlyVideoLimit(Auth::user()->id);

    $video_data = [
        'currentVideoCount' => $currentVideoCount,
        'maxVideoLimit' => $maxVideoLimit,
    ];

    return view('livewire.profile.subscription',[
        'chartData' => $chartData,
        'storage_data' =>$storage_data,
        'video_data' => $video_data
    ]);

    
}





}
