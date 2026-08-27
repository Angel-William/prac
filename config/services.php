<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | [EXAM] PSRS 1.e - the shared secret third parties send as X-API-Key.
    |
    | [LEARN] env() IS ONLY SAFE INSIDE config/ FILES.
    |         Once you run `php artisan config:cache`, env() returns null
    |         everywhere else in the app. So we read it here, once, and the
    |         middleware asks for config('services.api_key') instead.
    |         This is the single most common "it works locally but not in
    |         production" bug in Laravel.
    */
    'api_key' => env('API_KEY'),

];
