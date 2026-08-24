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
            'label' => 'My Profile',
            'icon' => 'fas fa-id-card',
            'route' => 'account.profile.edit',
            'active' => ['account.profile.*'],
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
            'label' => 'Submit Event',
            'icon' => 'fas fa-calendar-plus',
            'route' => 'account.events.create',
            'active' => ['account.events.create'],
        ],
        [
            'label' => 'My Events',
            'icon' => 'fas fa-calendar-days',
            'route' => 'account.events.index',
            'active' => ['account.events.*'],
        ],
        [
            'label' => 'Browse Aspirants',
            'icon' => 'fas fa-users',
            'route' => 'aspirants.public',
        ],
        [
            'label' => 'Download App',
            'icon' => 'fas fa-mobile-screen-button',
            'href' => 'https://play.google.com/store/apps/details?id=com.mlk.tukokadi',
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
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
