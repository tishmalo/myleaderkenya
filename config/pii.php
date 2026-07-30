<?php

return [
    // Set a stable application-specific secret for exact-match PII blind indexes.
    'index_key' => env('PII_INDEX_KEY'),
];
