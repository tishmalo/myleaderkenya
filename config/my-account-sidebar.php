<?php

return [
    'brand' => [
        'label' => 'My Leader Kenya',
        'title' => 'My Account',
        'icon' => 'fas fa-user-circle',
    ],

    'items' => [
        [
            'label' => 'Overview',
            'icon' => 'fas fa-house',
            'route' => 'my-account',
            'active' => ['my-account'],
        ],
        [
            'label' => 'Available Dashboards',
            'icon' => 'fas fa-gauge-high',
            'href' => '#dashboards',
        ],
        [
            'label' => 'Claim Requests',
            'icon' => 'fas fa-file-signature',
            'href' => '#claims',
        ],
        [
            'label' => 'Submit or Claim',
            'icon' => 'fas fa-user-plus',
            'route' => 'aspirants.register',
            'style' => 'primary',
        ],
        [
            'label' => 'Browse Aspirants',
            'icon' => 'fas fa-users',
            'route' => 'aspirants.public',
        ],
        [
            'label' => 'Back to Website',
            'icon' => 'fas fa-arrow-left',
            'route' => 'landing',
        ],
    ],

    'footer' => [
        'label' => 'Logout',
        'icon' => 'fas fa-sign-out-alt',
        'route' => 'logout',
    ],
];