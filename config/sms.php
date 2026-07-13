<?php

return [
    'infobip' => [
        'endpoint' => env('INFOBIP_SMS_ENDPOINT', '/restapi/sms/1/text/single'),
        'timeout' => (int) env('INFOBIP_SMS_TIMEOUT', 30),
        'chunk_size' => (int) env('INFOBIP_SMS_CHUNK_SIZE', 500),
    ],
];
