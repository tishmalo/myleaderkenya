<?php

/*
|--------------------------------------------------------------------------
| Spam Filter Configuration
|--------------------------------------------------------------------------
|
| Baseline spam rules for campaign-tool feature requests. Edit these values
| and redeploy to change the developer-set defaults. Additional rules added
| from the admin Spam Filter hub are stored in the database and merged with
| these at runtime.
|
*/

return [

    'enabled' => true,

    /*
    | Character limits applied to the feature-request form. These are
    | enforced both in the browser (client-side rejection) and on the server.
    */
    'character_limits' => [
        'requester_name' => 255,
        'email' => 255,
        'phone' => 50,
        'requested_feature' => 255,
        'use_case' => 2000,
    ],

    /*
    | Server-only content rules. These are never sent to the browser so
    | spammers cannot learn what triggers them.
    */
    'content' => [

        // Any of these phrases anywhere in the submission (case-insensitive).
        'blocked_keywords' => [
            'pay day cash advance',
            'cash advance',
            'quick approval',
            'flexible loan options',
            'affordable rates',
            'apply from the comfort of your home',
            'hello. i am glad meet everybody',
            'give some suguest',
            'good luck :)',
        ],

        // Reject submissions that mention any of these domains.
        'blocked_domains' => [
            'novvaloans.com',
            'everycalculators.com',
            'farironalds.com',
        ],

        // Reject submissions containing any URL not in the allowlist.
        'flag_any_url' => true,
        'url_allowlist' => [
            'myleader.co.ke',
            'tukokadi.co.ke',
        ],

        // Reject submissions containing HTML tags/attributes (e.g. <a href="...">).
        'flag_html' => true,

        // Email patterns that look automated/disposable.
        'suspicious_email_patterns' => [
            '/^\d+@/', // numeric local part: 1071@example.com
            '/@.*\.(xyz|top|loan|gq|ml|tk|cf|ga)$/i',
        ],

        // Kenyan mobile format. Set to null to disable the phone check.
        'phone_accept_regex' => '/^(?:\+?254|0)[17][0-9]{8}$/',

        // Phrases flagged inside the requester name.
        'blocked_name_phrases' => [
            'hello',
            'good luck',
            'plz',
        ],

        // Flag submissions where the same distinctive word repeats this many times.
        'repeated_phrase_threshold' => 5,
    ],

    // What happens when a content rule matches: 'reject' (hard reject, nothing saved).
    'content_action' => 'reject',

    /*
    | Client-side "obvious" checks. These ARE embedded in the page. Keep this
    | list minimal so spammers learn little from it.
    */
    'client' => [
        'enabled' => true,
        'block_url' => true,
        'block_html' => true,
        'blocked_domains' => [
            'novvaloans.com',
            'everycalculators.com',
        ],
        'blocked_keywords' => [
            'cash advance',
            'pay day loan',
        ],
        'rate_limit' => [
            'max' => 5,
            'window_minutes' => 60,
        ],
    ],

    /*
    | IP geolocation via ip-api.com plus the country policy.
    |  - Kenyan IPs: allowed.
    |  - Non-Kenyan / unknown: 'spam' (quarantine), 'reject', or 'allow'.
    */
    'ip_lookup' => [
        'enabled' => true,
        'provider' => 'ip_api',
        'ip_api' => [
            'endpoint' => 'http://ip-api.com/json/{ip}?fields=status,countryCode',
            'timeout_seconds' => 2,
            'cache_ttl_seconds' => 86400,
        ],
        'kenyan_country_codes' => ['KE'],
        'non_kenyan_action' => 'spam',
        'unknown_action' => 'spam',
    ],

    // Baseline blocked IPs (admin adds more from the spam hub).
    'blocked_ips' => [],

    // Custom 403 challenge shown to blocked IPs.
    'ip_challenge' => [
        'override_ttl_hours' => 24,
        // Paths that must not be intercepted (the challenge handler + static assets).
        'excluded_paths' => ['bot-verify', 'up', 'build', 'storage', 'fonts'],
    ],

    // How long flagged requests/samples are kept before the daily purge removes them.
    'retention_days' => 30,

    // Record every flagged submission as a spam sample for the admin hub.
    'record_samples' => true,
];