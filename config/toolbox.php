<?php

return [
    // Existing published tools without an explicit admin price remain sponsor-ready.
    'default_sponsorship_token_cost' => (int) env('TOOLBOX_DEFAULT_SPONSORSHIP_TOKEN_COST', 10),
];