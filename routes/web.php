<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DigitalAssetController;




use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use App\Models\PublishedMedia;



use App\Jobs\TestJob;
use App\Jobs\FailingJob;
use AdaiasMagdiel\MetaAI\Client;






use App\Http\Controllers\Platform\GoogleLoginController;
use App\Http\Controllers\Platform\GoogleServiceController;
use App\Http\Controllers\Platform\InstagramController;
use App\Http\Controllers\Platform\TiktokController;
use App\Http\Controllers\Platform\FacebookController;
use App\Http\Controllers\Platform\EbayController;
use App\Http\Controllers\Platform\EtsyController;




Route::get('/dispatch-test-job', function () {
    TestJob::dispatch();
    return 'Test job dispatched!';
});


Route::get('/dispatch-failing-job', function () {
    FailingJob::dispatch();
    return 'Failing job dispatched!';
});




Route::get('/image-test', function () {

        // create image manager with desired driver
        $manager = new ImageManager(new Driver());

        // read image from file system
        $image = $manager->read('example.jpg');
        // Image Crop
        $image->crop(500,500);

        //Save the file
        $image->save(public_path('bra.jpg'));

});


use App\Helper\Editor\StructureHelper;
use App\Helper\ShortFormGeneratorHelper;


Route::get('structure', function(){

$test = StructureHelper::init();

$test = ShortFormGeneratorHelper::clipTemplatePairStructure($test);

    return view('structure',[
        'project' => $test['project']
    ]);

});

Route::get('/meta-test', function () {

        $client = new Client();
        $response = $client->prompt(
    "Who is Bruce Wayne?", 
    stream: true
);
        dd($response);

});


Route::middleware([
    'setPageAttributes'
])->group(function () {




        Route::get('/', function () {
            return view('welcome');
        });




        Route::get('subscription-pricing',[SubscriptionController::class, 'pricing'])->name('subscription-story-pricing');




        // Contact

        Route::get('contact',function(){
            return view('contact.index');
        })->name('contact');





    Route::middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])->group(function () {



        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');



        // Digital Assets Project
        Route::get('user/digital-assets', function(){
            return view('profile.digital-assets.index');
        });


        Route::get('user/digital-assets/{id}',[DigitalAssetController::class, 'show']);



        Route::get('user/digital-assets/{id}/social-media', function($id){
            return view('profile.digital-assets.social-media',[
                'digital_asset_id'=>$id
            ]);
        });

        Route::get('user/digital-assets/{id}/connected-platforms', function($id){
            return view('profile.digital-assets.connected-platforms',[
                'digital_asset_id'=>$id
            ]);
        });


        Route::get('user/digital-assets/{id}/products', function($id){
            return view('profile.digital-assets.product',[
                'digital_asset_id'=>$id
            ]);
        });




        Route::get('user/digital-assets/{id}/published', function($id){
            return view('profile.digital-assets.published',[
                'digital_asset_id'=>$id
            ]);
        });




    Route::get('/download-published-media/{id}', function($id){

        // Add User Auth urgent!!



        $history = PublishedMedia::findOrFail($id);

        // published/combined_reel_user_1_20241126_040840.mp4
        $filePath = $history->url;

        // Check if the file exists
        if (Storage::disk('public')->exists($filePath)) {
            // Use Storage facade to download the file
            return Storage::disk('public')->download($filePath);
        }

        return back()->with('error', 'File not found.');

    })->name('download.published-media');








        // API/API Subscription
        Route::get('user/subscription',function(){
            return view('profile.subscription');
        });


        Route::get('subscription-upgrade/{subscription_id}',[SubscriptionController::class, 'upgrade']);


        Route::post('/user/subscription/update-payment-info',[SubscriptionController::class, 'stripe_subscription_payment_update']);
        
        Route::get('subscription-change/{subscription_id}',[SubscriptionController::class, 'change']);

        Route::post('subscription-init-change',[SubscriptionController::class, 'change_subscription']);

        Route::post('stripe-payment-subscription',[SubscriptionController::class, 'stripe_payment_subscription']);

        Route::get('stripe-payment-subscription/{plan_id}',[SubscriptionController::class, 'stripe_payment_subscription_v2']);

        // Paypal

        Route::post('paypal-payment-subscription',[SubscriptionController::class, 'paypal_payment_subscription']);







        // Social Media Login

        Route::get('google-youtube-login', [GoogleServiceController::class, 'redirectToGoogleForChannel']);

        Route::get('google-youtube-callback', [GoogleServiceController::class, 'handleGoogleChannelSuccess']);


        Route::get('instagram-login', [InstagramController::class, 'redirect']);

        Route::get('instagram-callback',[InstagramController::class, 'success']);



        Route::get('tiktok-login', [TiktokController::class, 'redirect']);

        Route::get('tiktok-callback',[TiktokController::class, 'success']);




        Route::get('facebook-login', [FacebookController::class, 'redirect']);

        Route::get('facebook-callback',[FacebookController::class, 'success']);



        // Platform Connect

        Route::get('connect-platform-ebay-login',[EbayController::class, 'redirect']);

        Route::get('connect-platform-ebay-callback',[EbayController::class, 'success']);


        Route::get('connect-platform-etsy-login',[EtsyController::class, 'redirect']);

        Route::get('connect-platform-etsy-callback',[EtsyController::class, 'success']);

        Route::get('connect-platform-amazon-login',[AmazonController::class, 'redirect']);

        Route::get('connect-platform-amazon-callback',[AmazonController::class, 'success']);


        Route::get('connect-platform-shopify-login',[ShopifyController::class, 'redirect']);

        Route::get('connect-platform-shopify-callback',[ShopifyController::class, 'success']);


        Route::get('connect-platform-amazon-login',[AmazonController::class, 'redirect']);

        Route::get('connect-platform-amazon-callback',[AmazonController::class, 'success']);


    });



    Route::middleware([
        'guest',
    ])->withoutMiddleware(['logout'])->group(function () {

        // Login

        Route::get('google-login', [GoogleLoginController::class, 'redirect']);

        Route::get('google-callback', [GoogleLoginController::class, 'success']);

        Route::get('google-link', [GoogleLoginController::class, 'link']);

        Route::post('google-linking', [GoogleLoginController::class, 'linking']);

    });







});