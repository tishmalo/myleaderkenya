<?php

return [
    'brand' => [
        'label' => 'Aspirant',
        'title' => 'Dashboard',
    ],

    'items' => [
        [
            'label' => 'Analytics',
            'icon' => 'fas fa-chart-line',
            'href' => '#analytics',
            'section' => 'analytics',
        ],
        [
            'label' => 'Campaign Tools',
            'icon' => 'fas fa-toolbox',
            'href' => '#campaign-tools',
            'section' => 'campaign-tools',
        ],
        [
            'label' => 'Bulk SMS',
            'icon' => 'fas fa-comment-sms',
            'route' => 'aspirant.tools.show',
            'params' => ['bulk-sms'],
            'active' => ['aspirant.tools.show'],
            'tool_key' => 'bulk-sms',
        ],
        [
            'label' => 'Opinion Polls',
            'icon' => 'fas fa-square-poll-vertical',
            'route' => 'aspirant.tools.show',
            'params' => ['opinion-polls'],
            'active' => ['aspirant.tools.show'],
            'tool_key' => 'opinion-polls',
        ],
        [
            'label' => 'Campaign Website',
            'icon' => 'fas fa-globe',
            'route' => 'aspirant.tools.show',
            'params' => ['campaign-website'],
            'active' => ['aspirant.tools.show'],
            'tool_key' => 'campaign-website',
        ],
        [
            'label' => 'Call Center',
            'icon' => 'fas fa-headset',
            'route' => 'aspirant.tools.show',
            'params' => ['call-center'],
            'active' => ['aspirant.tools.show'],
            'tool_key' => 'call-center',
        ],
        [
            'label' => 'Profile',
            'icon' => 'fas fa-id-badge',
            'href' => '#profile',
            'section' => 'profile',
        ],
        [
            'label' => 'Recent Outreach',
            'icon' => 'fas fa-paper-plane',
            'href' => '#recent-outreach',
            'section' => 'recent-outreach',
        ],
        [
            'label' => 'Poll Snapshot',
            'icon' => 'fas fa-chart-simple',
            'href' => '#poll-snapshot',
            'section' => 'poll-snapshot',
        ],        [
            'label' => 'Token Wallet',
            'icon' => 'fas fa-coins',
            'route' => 'aspirant.tokens.index',
            'active' => ['aspirant.tokens.*'],
        ],
    ],
];

