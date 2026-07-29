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

    'deepseek' => [
        'enabled' => (bool) env('DEEPSEEK_ENABLED', false),
        'api_key' => env('DEEPSEEK_API_KEY'),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-v4-flash'),
        'prompt_version' => env('DEEPSEEK_PUBLIC_PULSE_PROMPT_VERSION', 'pulse-tone-v1'),
        'connect_timeout' => (int) env('DEEPSEEK_CONNECT_TIMEOUT', 10),
        'timeout' => (int) env('DEEPSEEK_TIMEOUT', 60),
        'batch_size' => (int) env('DEEPSEEK_PUBLIC_PULSE_BATCH_SIZE', 30),
        'cache_ttl_days' => (int) env('DEEPSEEK_PUBLIC_PULSE_CACHE_TTL_DAYS', 90),
        'min_chars' => (int) env('DEEPSEEK_PUBLIC_PULSE_MIN_CHARS', 18),
        'max_chars_per_mention' => (int) env('DEEPSEEK_PUBLIC_PULSE_MAX_CHARS_PER_MENTION', 800),
        'max_tokens' => (int) env('DEEPSEEK_PUBLIC_PULSE_MAX_TOKENS', 3500),
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



