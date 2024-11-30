<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT')
    ],
    

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT')
    ],

    'instagram' => [    
      'client_id' => env('INSTAGRAM_CLIENT_ID'),  
      'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),  
      'redirect' => env('INSTAGRAM_REDIRECT_URI') 
    ],
    
    'instagrambasic' => [    
      'client_id' => env('INSTAGRAMBASIC_CLIENT_ID'),  
      'client_secret' => env('INSTAGRAMBASIC_CLIENT_SECRET'),  
      'redirect' => env('INSTAGRAMBASIC_REDIRECT_URI') 
    ],
    'etsy' => [    
      'client_id' => env('ETSY_CLIENT_ID'),  
      'client_secret' => env('ETSY_CLIENT_SECRET'),  
      'redirect' => env('ETSY_REDIRECT_URI') 
    ],

];
