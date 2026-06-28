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
                [
                    'label' => 'Live stats',
                    'route' => 'landing',
                    'fragment' => 'analytics',
                    'active' => ['landing'],
                ],
                [
                    'label' => 'Download App',
                    'route' => 'landing',
                    'fragment' => 'download-app',
                ],
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
            'route' => 'landing',
            'fragment' => 'campaign-tools',
            'children' => [
                ['label' => 'Campaign call center', 'route' => 'landing', 'fragment' => 'campaign-call-center'],
                ['label' => 'Bulk SMS', 'route' => 'landing', 'fragment' => 'bulk-sms'],
                ['label' => 'Bulk WhatsApp', 'route' => 'landing', 'fragment' => 'bulk-whatsapp'],
                ['label' => 'Domain and website', 'route' => 'landing', 'fragment' => 'domain-and-website'],
                ['label' => 'Databases and opinion polls', 'route' => 'landing', 'fragment' => 'databases-opinion-polls'],
                ['label' => 'Profile management (Aspirants)', 'route' => 'landing', 'fragment' => 'profile-management'],
            ],
        ],
        [
            'label' => 'Parties',
            'route' => 'landing',
            'fragment' => 'parties',
            'children' => [
                ['label' => 'Coalitions', 'route' => 'landing', 'fragment' => 'coalitions'],
                ['label' => 'Political parties', 'route' => 'landing', 'fragment' => 'political-parties'],
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