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

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'bagisto' => [
        'base_url' => env('BAGISTO_API_URL', 'https://merlinrod.com/api'),
        'email'    => env('BAGISTO_API_EMAIL'),
        'password' => env('BAGISTO_API_PASSWORD'),
    ],



    'numrot' => [
        'url' => env('NUMROT_URL'),
        'user' => env('NUMROT_USER'),
        'password' => env('NUMROT_PASSWORD'),
        'empresa_nit' => env('NUMROT_EMPRESA_NIT'),
    ],

    'auco' => [
        'base_url' => env('AUCO_BASE_URL', 'https://dev.auco.ai/v1.5/ext'),
        'private_key' => env('AUCO_PRIVATE_KEY'),
        'owner_email' => env('AUCO_OWNER_EMAIL'),
    ],
    
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
