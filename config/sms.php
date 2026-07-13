<?php

return [
    'provider' => env('SMS_PROVIDER', 'infobip'),

    'infobip' => [
        'base_url' => env('SMS_BASE_URL'),
        'username' => env('SMS_USERNAME'),
        'password' => env('SMS_PASSWORD'),
        'sender' => env('SMS_SENDER'),
        'endpoint' => env('INFOBIP_SMS_ENDPOINT', '/restapi/sms/1/text/single'),
        'timeout' => (int) env('INFOBIP_SMS_TIMEOUT', 30),
        'chunk_size' => (int) env('INFOBIP_SMS_CHUNK_SIZE', 500),
    ],
];
