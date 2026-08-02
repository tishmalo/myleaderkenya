<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Canonical regional/economic blocs
    |--------------------------------------------------------------------------
    |
    | These are the public-facing regions used for aspirant navigation and
    | county/candidate grouping. Keep this list as the source of truth so the
    | website menu and admin data screens do not drift into mixed province,
    | tribal, or ad-hoc labels.
    |
    */
    'names' => [
        'Nairobi',
        'North Eastern',
        'Coast',
        'South Eastern',
        'Mt. Kenya',
        'Central',
        'Eastern',
        'North Rift (NOREB)',
        'Rift Valley',
        'Lake Region (LREB)',
        'Nyanza',
        'Western',
    ],

    'aliases' => [
        'Lake Region' => 'Lake Region (LREB)',
        'North Rift' => 'North Rift (NOREB)',
        'NOREB' => 'North Rift (NOREB)',
        'LREB' => 'Lake Region (LREB)',
        'Mt Kenya' => 'Mt. Kenya',
        'Mount Kenya' => 'Mt. Kenya',
    ],
];
