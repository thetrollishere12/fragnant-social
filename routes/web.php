<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\GoogleLoginController;



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


Route::get('/', function () {
    return view('welcome');
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