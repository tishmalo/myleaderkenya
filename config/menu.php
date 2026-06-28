<?php

return [
    'frontend' => [
        [
            'label' => 'About Us',
            'route' => 'landing',
            'fragment' => 'about',
            'active' => ['landing'],
        ],
        [
            'label' => 'Voter',
            'children' => [
                ['label' => 'Live stats', 'route' => 'landing', 'fragment' => 'analytics', 'active' => ['landing']],
                ['label' => 'Download App', 'route' => 'landing', 'fragment' => 'download-app'],
            ],
        ],
        [
            'label' => 'Aspirants',
            'route' => 'aspirants.public',
            'active' => ['aspirants.public', 'aspirants.show'],
            'children' => [
                ['label' => 'Presidential', 'route' => 'aspirants.public', 'query' => ['position' => 'presidential']],
                ['label' => 'Governor', 'route' => 'aspirants.public', 'query' => ['position' => 'governor']],
                ['label' => 'Senator', 'route' => 'aspirants.public', 'query' => ['position' => 'senator']],
                ['label' => 'Women Rep', 'route' => 'aspirants.public', 'query' => ['position' => 'women-rep']],
                ['label' => 'Mp', 'route' => 'aspirants.public', 'query' => ['position' => 'mp']],
                ['label' => 'MCA', 'route' => 'aspirants.public', 'query' => ['position' => 'mca']],
            ],
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
            'route' => 'landing',
            'fragment' => 'contact-us',
        ],
    ],
];
