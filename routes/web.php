<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\SubscriptionController;


use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use App\Models\PublishedMedia;



use App\Jobs\TestJob;
use App\Jobs\FailingJob;




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



Route::middleware([
    'setPageAttributes'
])->group(function () {




    Route::get('/', function () {
        return view('welcome');
    });




    


    Route::get('subscription-pricing',[SubscriptionController::class, 'pricing'])->name('subscription-story-pricing');




    

    // Contact

    // Route::get('contact','ContactController@contact')->name('contact');

    // Route::post('send-contact','ContactController@send_contact');






});



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {



    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');



    // Project
    Route::get('user/media', function(){
        return view('profile.media.index');
    });


    Route::get('user/media-setting', function(){
        return view('profile.media.setting');
    });


    Route::get('user/published', function(){
        return view('profile.media.published',[
            'published' => PublishedMedia::where('user_id',Auth::user()->id)->get()
        ]);
    });




Route::get('/download-published-media/{id}', function($id){

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
    
    Route::post('subscription-change',[SubscriptionController::class, 'change']);

    Route::post('stripe-payment-subscription',[SubscriptionController::class, 'stripe_payment_subscription']);

    Route::get('stripe-payment-subscription/{plan_id}',[SubscriptionController::class, 'stripe_payment_subscription_v2']);

    // Paypal

    Route::post('paypal-payment-subscription',[SubscriptionController::class, 'paypal_payment_subscription']);










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