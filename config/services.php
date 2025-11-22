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

    'fonnte' => [
        'url' => env('FONNTE_URL', 'https://api.fonnte.com'),
        'api_key' => env('FONNTE_API_KEY'),
        'delay_between_messages' => env('FONNTE_DELAY_BETWEEN_MESSAGES', 3), // Detik
        'max_messages_per_period' => env('FONNTE_MAX_MESSAGES_PER_PERIOD', 10), // Max pesan per periode
        'rate_limit_period' => env('FONNTE_RATE_LIMIT_PERIOD', 5), // Periode dalam menit
    ],

];
