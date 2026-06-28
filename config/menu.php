<?php

return [
    'frontend' => [
        [
            'label' => 'About Us',
            'route' => 'about.public',
            'active' => ['about.public'],
        ],
        [
            'label' => 'Voter',
            'children' => [
                ['label' => 'Live stats', 'route' => 'live-stats.public', 'active' => ['live-stats.public']],
                ['label' => 'Download App', 'route' => 'download-app.public', 'active' => ['download-app.public']],
            ],
        ],
        [
            'label' => 'Aspirants',
            'route' => 'aspirants.public',
            'active' => ['aspirants.public', 'aspirants.show'],
            'dynamic' => 'positions',
        ],
        [
            'label' => 'News',
            'route' => 'news.public',
            'active' => ['news.public', 'news.public.show'],
        ],
        [
            'label' => 'Campaign tools',
            'route' => 'campaign-tools.public',
            'active' => ['campaign-tools.public', 'campaign-tools.show'],
            'dynamic' => 'campaign_tools',
        ],
        [
            'label' => 'Parties',
            'route' => 'parties.public',
            'active' => ['parties.public', 'parties.show', 'coalitions.public', 'coalitions.show'],
            'children' => [
                ['label' => 'Coalitions', 'route' => 'coalitions.public', 'active' => ['coalitions.public', 'coalitions.show']],
                ['label' => 'Political parties', 'route' => 'parties.public', 'active' => ['parties.public', 'parties.show']],
                ['label' => 'Partners', 'route' => 'landing', 'fragment' => 'partners'],
            ],
        ],
        [
            'label' => 'Contact us',
            'route' => 'contact.public',
            'active' => ['contact.public'],
        ],
    ],
];
