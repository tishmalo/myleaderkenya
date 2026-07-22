<?php

return [
    'sidebar' => [
        'brand' => [
            'name' => 'ML KENYA',
            'tagline' => 'THE KENYA. WE WANT',
            'logo' => 'images/myleader.png',
        ],

        'overview' => [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'fas fa-chart-line',
            'active' => ['dashboard'],
        ],

        'sections' => [
            [
                'label' => 'Access Control',
                'items' => [
                    ['label' => 'Admins, Roles & Permissions', 'route' => 'user-access.index', 'icon' => 'fas fa-user-shield', 'active' => ['user-access.*']],
                ],
            ],
            [
                'label' => 'Voter Management',
                'items' => [
                    ['label' => 'Voters', 'route' => 'users.index', 'icon' => 'fas fa-user-check', 'active' => ['users.*']],
                    ['label' => 'Voter Statistics', 'route' => 'dashboard.stats', 'icon' => 'fas fa-chart-pie', 'active' => ['dashboard.stats']],
                    ['label' => 'Voter Locations', 'route' => 'locations.index', 'icon' => 'fas fa-map', 'active' => ['locations.*']],
                    ['label' => 'Live Stat Figures', 'route' => 'live-stat-figures.index', 'icon' => 'fas fa-sliders', 'active' => ['live-stat-figures.*']],
                ],
            ],
            [
                'label' => 'Communications',
                'items' => [
                    ['label' => 'Voter Messages', 'route' => 'dashboard.messages', 'icon' => 'fas fa-comment-dots', 'active' => ['dashboard.messages', 'messages.*']],
                    ['label' => 'Create Message', 'route' => 'messages.create', 'icon' => 'fas fa-paper-plane', 'active' => ['messages.create']],
                    ['label' => 'Groups', 'route' => 'groups.create', 'icon' => 'fas fa-users', 'active' => ['groups.*']],
                    ['label' => 'SMS Balance Requests', 'route' => 'sms-balance-requests.index', 'icon' => 'fas fa-comment-sms', 'active' => ['sms-balance-requests.*']],
                ],
            ],
            [
                'label' => 'Aspirant Campaigns',
                'items' => [
                    ['label' => 'Candidates', 'route' => 'candidates.index', 'icon' => 'fas fa-id-badge', 'active' => ['candidates.*']],
                    ['label' => 'Positions', 'route' => 'positions.index', 'icon' => 'fas fa-user-tie', 'active' => ['positions.*']],
                    ['label' => 'Campaign Tools', 'route' => 'campaign-tools.index', 'icon' => 'fas fa-bullhorn', 'active' => ['campaign-tools.*']],
                    ['label' => 'Website Requests', 'route' => 'campaign-website-requests.index', 'icon' => 'fas fa-globe', 'active' => ['campaign-website-requests.*']],
                    ['label' => 'Website Samples', 'route' => 'campaign-website-samples.index', 'icon' => 'fas fa-images', 'active' => ['campaign-website-samples.*']],
                ],
            ],
            [
                'label' => 'Token Management',
                'items' => [
                    ['label' => 'Token Packages', 'route' => 'candidate-token-packages.index', 'icon' => 'fas fa-box', 'active' => ['candidate-token-packages.*']],
                    ['label' => 'Token Rates', 'route' => 'candidate-token-rates.index', 'icon' => 'fas fa-sliders', 'active' => ['candidate-token-rates.*']],
                    ['label' => 'Token Purchases', 'route' => 'candidate-token-purchases.index', 'icon' => 'fas fa-receipt', 'active' => ['candidate-token-purchases.*']],
                    ['label' => 'Token Ledger', 'route' => 'candidate-token-ledger.index', 'icon' => 'fas fa-list', 'active' => ['candidate-token-ledger.*']],
                ],
            ],
            [
                'label' => 'Public Content',
                'items' => [
                    ['label' => 'Frontend Pages', 'route' => 'frontend-pages.index', 'icon' => 'fas fa-file-alt', 'active' => ['frontend-pages.*']],
                    ['label' => 'News', 'route' => 'news.index', 'icon' => 'fas fa-newspaper', 'active' => ['news.*']],
                    ['label' => 'Tags', 'route' => 'tags.index', 'icon' => 'fas fa-tags', 'active' => ['tags.*']],
                ],
            ],
            [
                'label' => 'Finance',
                'items' => [
                    ['label' => 'Payment Methods', 'route' => 'payment-methods.index', 'icon' => 'fas fa-credit-card', 'active' => ['payment-methods.*']],
                    ['label' => 'Donors', 'route' => 'donors.index', 'icon' => 'fas fa-hand-holding-heart', 'active' => ['donors.*']],
                    ['label' => 'Dashboard Donors', 'route' => 'dashboard.donors', 'icon' => 'fas fa-chart-column', 'active' => ['dashboard.donors']],
                ],
            ],
            [
                'label' => 'Political Structures',
                'items' => [
                    ['label' => 'Political Parties', 'route' => 'political-parties.index', 'icon' => 'fas fa-landmark', 'active' => ['political-parties.*']],
                    ['label' => 'Coalitions', 'route' => 'coalitions.index', 'icon' => 'fas fa-handshake', 'active' => ['coalitions.*']],
                ],
            ],
            [
                'label' => 'Geography Data',
                'items' => [
                    ['label' => 'Regional Blocs', 'route' => 'blocs.index', 'icon' => 'fas fa-layer-group', 'active' => ['blocs.*']],
                    ['label' => 'Counties', 'route' => 'counties.index', 'icon' => 'fas fa-map', 'active' => ['counties.*']],
                    ['label' => 'Constituencies', 'route' => 'constituencies.index', 'icon' => 'fas fa-map-marker-alt', 'active' => ['constituencies.*']],
                    ['label' => 'Wards', 'route' => 'wards.index', 'icon' => 'fas fa-location-dot', 'active' => ['wards.*']],
                    ['label' => 'Polling Stations', 'route' => 'dashboard.stations', 'icon' => 'fas fa-building-flag', 'active' => ['dashboard.stations', 'stations.*']],
                ],
            ],
            [
                'label' => 'System Settings',
                'items' => [
                    ['label' => 'SMTP Settings', 'route' => 'admin.smtp', 'icon' => 'fas fa-envelope-circle-check', 'active' => ['admin.smtp']],
                ],
            ],
        ],
    ],
];