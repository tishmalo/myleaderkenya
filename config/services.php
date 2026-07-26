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
    'politiqkenya' => [
        'public_approval_endpoint' => env('POLITIQKENYA_PUBLIC_APPROVAL_ENDPOINT', 'https://api.politiqkenya.com/public/monitored-profile-example'),
        'connect_timeout' => (int) env('POLITIQKENYA_CONNECT_TIMEOUT', 15),
        'timeout' => (int) env('POLITIQKENYA_TIMEOUT', 120),
    ],

    'ipay' => [
        'vendor_id' => env('IPAY_VENDOR_ID'),
        'security_key' => env('IPAY_SECURITY_KEY'),
        'live' => (bool) env('IPAY_LIVE', false),
        'currency' => env('IPAY_CURRENCY', 'KES'),
        'checkout_url' => env('IPAY_CHECKOUT_URL', 'https://payments.ipayafrica.com/v3/ke'),
        'status_url' => env('IPAY_STATUS_URL', 'https://apis.ipayafrica.com/payments/v2/transaction/search'),
        'timeout' => (int) env('IPAY_TIMEOUT', 30),
    ],
];

