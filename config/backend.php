<?php

return [
    'sidebar' => [
        'brand' => [
            'name' => 'ML KENYA',
            'tagline' => 'THE KENYA. WE WANT',
            'logo' => 'images/myleader.png',
        ],

        'overview' => [
            'label' => 'Overview',
            'route' => 'dashboard',
            'icon' => 'fas fa-chart-line',
            'active' => ['dashboard'],
        ],

        'sections' => [
            [
                'label' => 'Voters',
                'items' => [
                    ['label' => 'Voters', 'route' => 'users.index', 'icon' => 'fas fa-user-check', 'active' => ['users.*']],
                    ['label' => 'Voter Messages', 'route' => 'dashboard.messages', 'icon' => 'fas fa-comment-dots', 'active' => ['dashboard.messages', 'messages.*']],
                    ['label' => 'Voter Stats', 'route' => 'dashboard.stats', 'icon' => 'fas fa-chart-pie', 'active' => ['dashboard.stats']],
                    ['label' => 'Voter Locations', 'route' => 'locations.index', 'icon' => 'fas fa-map', 'active' => ['locations.*']],
                ],
            ],
            [
                'label' => 'Aspirants',
                'items' => [
                    ['label' => 'Positions', 'route' => 'positions.index', 'icon' => 'fas fa-user-tie', 'active' => ['positions.*']],
                    ['label' => 'Candidates', 'route' => 'candidates.index', 'icon' => 'fas fa-id-badge', 'active' => ['candidates.*']],
                ],
            ],
            [
                'label' => 'Parties',
                'items' => [
                    ['label' => 'Parties', 'route' => 'political-parties.index', 'icon' => 'fas fa-landmark', 'active' => ['political-parties.*']],
                    ['label' => 'Coalitions', 'route' => 'coalitions.index', 'icon' => 'fas fa-handshake', 'active' => ['coalitions.*']],
                ],
            ],
            [
                'label' => 'Front End Settings',
                'items' => [
                    ['label' => 'Frontend Pages', 'route' => 'frontend-pages.index', 'icon' => 'fas fa-file-alt', 'active' => ['frontend-pages.*']],
                    ['label' => 'Campaign Tools', 'route' => 'campaign-tools.index', 'icon' => 'fas fa-bullhorn', 'active' => ['campaign-tools.*']],
                    ['label' => 'Donor Settings', 'route' => 'payment-methods.index', 'icon' => 'fas fa-money-bill-wave', 'active' => ['payment-methods.*', 'donors.*']],
                    ['label' => 'News', 'route' => 'news.index', 'icon' => 'fas fa-newspaper', 'active' => ['news.*']],
                    ['label' => 'Tags', 'route' => 'tags.index', 'icon' => 'fas fa-tags', 'active' => ['tags.*']],
                ],
            ],
            [
                'label' => 'Data',
                'items' => [
                    ['label' => 'Blocs', 'route' => 'blocs.index', 'icon' => 'fas fa-users', 'active' => ['blocs.*']],
                    ['label' => 'Counties', 'route' => 'counties.index', 'icon' => 'fas fa-map', 'active' => ['counties.*']],
                    ['label' => 'Constituencies', 'route' => 'constituencies.index', 'icon' => 'fas fa-map-marker-alt', 'active' => ['constituencies.*']],
                    ['label' => 'Wards', 'route' => 'wards.index', 'icon' => 'fas fa-layer-group', 'active' => ['wards.*']],
                    ['label' => 'Polling Stations', 'route' => 'dashboard.stations', 'icon' => 'fas fa-location-dot', 'active' => ['dashboard.stations', 'stations.*']],
                ],
            ],
        ],
    ],
];

