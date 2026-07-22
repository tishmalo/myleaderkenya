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
            'active' => ['dashboard'], 'permission' => 'dashboard.view',
        ],

        'sections' => [
            [
                'label' => 'Access Control',
                'items' => [
                    ['label' => 'Admins, Roles & Permissions', 'route' => 'user-access.index', 'icon' => 'fas fa-user-shield', 'active' => ['user-access.*'], 'permission' => 'user-access.view'],
                ],
            ],
            [
                'label' => 'Voter Management',
                'items' => [
                    ['label' => 'Voters', 'route' => 'users.index', 'icon' => 'fas fa-user-check', 'active' => ['users.*'], 'permission' => 'voters.view'],
                    ['label' => 'Voter Statistics', 'route' => 'dashboard.stats', 'icon' => 'fas fa-chart-pie', 'active' => ['dashboard.stats'], 'permission' => 'voters.view'],
                    ['label' => 'Voter Locations', 'route' => 'locations.index', 'icon' => 'fas fa-map', 'active' => ['locations.*'], 'permission' => 'voters.view'],
                    ['label' => 'Live Stat Figures', 'route' => 'live-stat-figures.index', 'icon' => 'fas fa-sliders', 'active' => ['live-stat-figures.*'], 'permission' => 'live-stats.view'],
                ],
            ],
            [
                'label' => 'Communications',
                'items' => [
                    ['label' => 'Voter Messages', 'route' => 'dashboard.messages', 'icon' => 'fas fa-comment-dots', 'active' => ['dashboard.messages', 'messages.*'], 'permission' => 'messages.view'],
                    ['label' => 'Create Message', 'route' => 'messages.create', 'icon' => 'fas fa-paper-plane', 'active' => ['messages.create'], 'permission' => 'messages.create'],
                    ['label' => 'Groups', 'route' => 'groups.create', 'icon' => 'fas fa-users', 'active' => ['groups.*'], 'permission' => 'messages.create'],
                    ['label' => 'SMS Balance Requests', 'route' => 'sms-balance-requests.index', 'icon' => 'fas fa-comment-sms', 'active' => ['sms-balance-requests.*'], 'permission' => 'messages.view'],
                ],
            ],
            [
                'label' => 'Aspirant Campaigns',
                'items' => [
                    ['label' => 'Candidates', 'route' => 'candidates.index', 'icon' => 'fas fa-id-badge', 'active' => ['candidates.*'], 'permission' => 'aspirants.view'],
                    ['label' => 'Positions', 'route' => 'positions.index', 'icon' => 'fas fa-user-tie', 'active' => ['positions.*'], 'permission' => 'aspirants.view'],
                    ['label' => 'Campaign Tools', 'route' => 'campaign-tools.index', 'icon' => 'fas fa-bullhorn', 'active' => ['campaign-tools.*'], 'permission' => 'aspirants.view'],
                    ['label' => 'Website Requests', 'route' => 'campaign-website-requests.index', 'icon' => 'fas fa-globe', 'active' => ['campaign-website-requests.*'], 'permission' => 'aspirants.view'],
                    ['label' => 'Website Samples', 'route' => 'campaign-website-samples.index', 'icon' => 'fas fa-images', 'active' => ['campaign-website-samples.*'], 'permission' => 'aspirants.view'],
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
                    ['label' => 'Frontend Pages', 'route' => 'frontend-pages.index', 'icon' => 'fas fa-file-alt', 'active' => ['frontend-pages.*'], 'permission' => 'frontend.view'],
                    ['label' => 'News', 'route' => 'news.index', 'icon' => 'fas fa-newspaper', 'active' => ['news.*'], 'permission' => 'frontend.view'],
                    ['label' => 'Tags', 'route' => 'tags.index', 'icon' => 'fas fa-tags', 'active' => ['tags.*'], 'permission' => 'frontend.view'],
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
                    ['label' => 'Political Parties', 'route' => 'political-parties.index', 'icon' => 'fas fa-landmark', 'active' => ['political-parties.*'], 'permission' => 'parties.view'],
                    ['label' => 'Coalitions', 'route' => 'coalitions.index', 'icon' => 'fas fa-handshake', 'active' => ['coalitions.*'], 'permission' => 'parties.view'],
                ],
            ],
            [
                'label' => 'Geography Data',
                'items' => [
                    ['label' => 'Regional Blocs', 'route' => 'blocs.index', 'icon' => 'fas fa-layer-group', 'active' => ['blocs.*'], 'permission' => 'data.view'],
                    ['label' => 'Counties', 'route' => 'counties.index', 'icon' => 'fas fa-map', 'active' => ['counties.*'], 'permission' => 'data.view'],
                    ['label' => 'Constituencies', 'route' => 'constituencies.index', 'icon' => 'fas fa-map-marker-alt', 'active' => ['constituencies.*'], 'permission' => 'data.view'],
                    ['label' => 'Wards', 'route' => 'wards.index', 'icon' => 'fas fa-location-dot', 'active' => ['wards.*'], 'permission' => 'data.view'],
                    ['label' => 'Polling Stations', 'route' => 'dashboard.stations', 'icon' => 'fas fa-building-flag', 'active' => ['dashboard.stations', 'stations.*'], 'permission' => 'data.view'],
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
