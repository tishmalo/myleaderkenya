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
            'label' => 'Submit or Claim',
            'icon' => 'fas fa-user-plus',
            'route' => 'aspirants.register',
            'style' => 'primary',
        ],
        [
            'label' => 'My Toolbox',
            'icon' => 'fas fa-toolbox',
            'route' => 'account.toolbox.index',
            'active' => ['account.toolbox.*'],
        ],
        [
            'label' => 'Submit News',
            'icon' => 'fas fa-pen-to-square',
            'route' => 'account.news.create',
            'active' => ['account.news.create'],
        ],
        [
            'label' => 'My News',
            'icon' => 'fas fa-newspaper',
            'route' => 'account.news.index',
            'active' => ['account.news.index'],
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
