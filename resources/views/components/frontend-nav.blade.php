@php
    $menuItems = config('menu.frontend', []);

    foreach ($menuItems as &$menuItem) {
        $dynamicType = $menuItem['dynamic'] ?? null;

        if (! in_array($dynamicType, ['campaign_tools', 'positions'], true)) {
            continue;
        }

        $menuItem['children'] = [];

        try {
            if ($dynamicType === 'campaign_tools' && class_exists(\App\Models\CampaignTool::class) && Route::has('campaign-tools.show')) {
                $menuItem['children'] = \App\Models\CampaignTool::published()
                    ->ordered()
                    ->get()
                    ->map(fn ($tool) => [
                        'label' => $tool->nav_title,
                        'route' => 'campaign-tools.show',
                        'query' => ['slug' => $tool->slug],
                        'active' => ['campaign-tools.show'],
                    ])
                    ->all();
            }
            if ($dynamicType === 'positions' && class_exists(\App\Models\Position::class) && Route::has('aspirants.public')) {
                $regionalBlocNames = config('regional-blocs.names', []);
                $regionalBlocs = class_exists(\App\Models\Bloc::class)
                    ? \App\Models\Bloc::query()
                        ->when($regionalBlocNames, fn ($query) => $query->whereIn('name', $regionalBlocNames))
                        ->get(['id', 'name'])
                        ->sortBy(fn ($bloc) => array_search($bloc->name, $regionalBlocNames, true))
                        ->values()
                    : collect();

                $menuItem['children'] = \App\Models\Position::ordered()
                    ->get()
                    ->map(function ($position) use ($regionalBlocs) {
                        $positionName = strtolower(trim($position->name));
                        $positionItem = [
                            'label' => $position->name,
                            'route' => 'aspirants.public',
                            'query' => ['position' => $position->id],
                            'active' => ['aspirants.public', 'aspirants.show'],
                        ];

                        if (! str_contains($positionName, 'president') && $regionalBlocs->isNotEmpty()) {
                            $positionItem['children'] = $regionalBlocs
                                ->map(fn ($bloc) => [
                                    'label' => $bloc->name,
                                    'route' => 'aspirants.public',
                                    'query' => ['position' => $position->id, 'bloc' => $bloc->id],
                                    'active' => ['aspirants.public'],
                                ])
                                ->all();
                        }

                        return $positionItem;
                    })
                    ->all();
            }


        } catch (\Throwable $e) {
            $menuItem['children'] = [];
        }
    }
    unset($menuItem);

    $joinNowUrl = config('constants.app_download_link');
    $buildMenuUrl = function (array $item): string {
        $query = $item['query'] ?? [];

        if (! empty($item['route']) && Route::has($item['route'])) {
            $url = route($item['route'], $query);
        } elseif (! empty($item['url'])) {
            $url = url($item['url']);
            if (! empty($query)) {
                $url .= '?' . http_build_query($query);
            }
        } else {
            $url = '#';
        }

        if (! empty($item['fragment'])) {
            $url .= '#' . ltrim($item['fragment'], '#');
        }

        return $url;
    };

    $isMenuActive = function (array $item): bool {
        foreach (($item['active'] ?? []) as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        if (! empty($item['route']) && request()->routeIs($item['route'])) {
            return true;
        }

        foreach (($item['children'] ?? []) as $child) {
            foreach (($child['active'] ?? []) as $pattern) {
                if (request()->routeIs($pattern)) {
                    return true;
                }
            }

            if (! empty($child['route']) && request()->routeIs($child['route'])) {
                return true;
            }
        }

        return false;
    };
@endphp
<style>
.frontend-nav {
    background: rgba(10,10,10,0.97);
    border-bottom: 1px solid rgba(255,255,255,0.07);
    backdrop-filter: blur(16px);
    position: sticky;
    top: 0;
    z-index: 100;
}
.frontend-nav-inner {
    max-width: 1280px;
    margin: 0 auto;
    padding: 16px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
}
.frontend-nav-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    flex-shrink: 0;
}
.frontend-nav-brand-logo {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.frontend-nav-brand-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.35));
}
.frontend-nav-brand-name {
    font-family: 'Oswald', sans-serif;
    font-size: 19px;
    font-weight: 700;
    color: white;
    letter-spacing: 1px;
    white-space: nowrap;
}
.frontend-nav-menu {
    display: flex;
    align-items: center;
    gap: 4px;
    list-style: none;
    margin: 0;
    padding: 0;
}
.frontend-nav-item { position: relative; }
.frontend-nav-link,
.frontend-nav-trigger {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 12px 10px;
    border: 0;
    background: transparent;
    color: rgba(255,255,255,0.62);
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-decoration: none;
    text-transform: uppercase;
    transition: color 0.2s, background 0.2s;
    white-space: nowrap;
}
.frontend-nav-link:hover,
.frontend-nav-trigger:hover,
.frontend-nav-item.active > .frontend-nav-link,
.frontend-nav-item.active > .frontend-nav-trigger {
    color: white;
}
.frontend-nav-chevron { font-size: 10px; opacity: 0.7; }
.frontend-nav-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    min-width: 230px;
    padding: 8px;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    background: rgba(15,15,15,0.98);
    box-shadow: 0 22px 50px rgba(0,0,0,0.42);
    opacity: 0;
    visibility: hidden;
    transform: translateY(6px);
    transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s;
}
.frontend-nav-subdropdown::-webkit-scrollbar {
    width: 6px;
}
.frontend-nav-subdropdown::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: rgba(255,255,255,0.18);
}
.frontend-nav-item:hover > .frontend-nav-dropdown,
.frontend-nav-item:focus-within > .frontend-nav-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.frontend-nav-dropdown a,
.frontend-nav-dropdown-trigger {
    display: block;
    padding: 10px 12px;
    border-radius: 6px;
    color: rgba(255,255,255,0.66);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.2px;
    text-decoration: none;
    text-transform: none;
    transition: color 0.2s, background 0.2s;
}
.frontend-nav-dropdown-trigger {
    width: 100%;
    border: 0;
    text-align: left;
    background: transparent;
    cursor: default;
}
.frontend-nav-dropdown-row { position: relative; }
.frontend-nav-dropdown-row.has-children > a,
.frontend-nav-dropdown-row.has-children > .frontend-nav-dropdown-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.frontend-nav-subdropdown {
    position: absolute;
    top: -8px;
    left: calc(100% + 8px);
    min-width: 240px;
    max-height: calc(100vh - 170px);
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: 8px;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    background: rgba(15,15,15,0.98);
    box-shadow: 0 22px 50px rgba(0,0,0,0.42);
    opacity: 0;
    visibility: hidden;
    transform: translateX(-4px);
    transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s;
}
.frontend-nav-dropdown-row:hover > .frontend-nav-subdropdown,
.frontend-nav-dropdown-row:focus-within > .frontend-nav-subdropdown {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
}
.frontend-nav-dropdown a:hover,
.frontend-nav-dropdown-trigger:hover {
    background: rgba(255,255,255,0.06);
    color: var(--green-bright, #00A86B);
}
.frontend-nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.frontend-nav-actions .btn-ghost,
.frontend-nav-actions .btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 9px 18px;
    border: 0;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-decoration: none;
    text-transform: uppercase;
}
.frontend-nav-actions .btn-ghost {
    background: transparent;
    color: rgba(255,255,255,0.62);
}
.frontend-nav-actions .btn-ghost:hover { color: white; }
.frontend-nav-actions .btn-primary {
    background: var(--kenya-red, #BB0000);
    color: white;
}
.frontend-nav-actions .btn-primary:hover { background: #cc0000; }
.frontend-nav-mobile-toggle { display: none; }
.frontend-nav-mobile-panel { display: none; }

@media (max-width: 1100px) {
    .frontend-nav-inner { padding: 14px 20px; }
    .frontend-nav-menu,
    .frontend-nav-actions { display: none; }
    .frontend-nav-mobile-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 8px;
        background: rgba(255,255,255,0.04);
        color: white;
        cursor: pointer;
    }
    .frontend-nav-mobile-panel.open {
        display: block;
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 20px 18px;
    }
    .frontend-nav-mobile-link,
    .frontend-nav-mobile-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 13px 0;
        border-top: 1px solid rgba(255,255,255,0.08);
        color: rgba(255,255,255,0.78);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1px;
        text-decoration: none;
        text-transform: uppercase;
    }
    .frontend-nav-mobile-group summary { cursor: pointer; list-style: none; }
    .frontend-nav-mobile-group summary::-webkit-details-marker { display: none; }
    .frontend-nav-mobile-children { padding: 0 0 8px 14px; }
    .frontend-nav-mobile-children a {
        display: block;
        padding: 9px 0;
        color: rgba(255,255,255,0.58);
        font-size: 13px;
        text-decoration: none;
    }
    .frontend-nav-mobile-grandchildren {
        padding: 0 0 4px 14px;
    }
    .frontend-nav-mobile-grandchildren a {
        font-size: 12px;
        color: rgba(255,255,255,0.48);
    }
}
</style>

<nav class="frontend-nav" data-frontend-nav>
    <div class="frontend-nav-inner">
        <a href="{{ route('landing') }}" class="frontend-nav-brand" aria-label="My Leader Kenya home">
            <div class="frontend-nav-brand-logo"><img src="{{ asset('images/mlkfav.png') }}" alt="My Leader Kenya"></div>
            <div class="frontend-nav-brand-name">MY LEADER KENYA</div>
        </a>

        <ul class="frontend-nav-menu" aria-label="Primary navigation">
            @foreach($menuItems as $item)
                @php
                    $children = $item['children'] ?? [];
                @endphp
                <li class="frontend-nav-item {{ $isMenuActive($item) ? 'active' : '' }}">
                    @if($children)
                        <a href="{{ $buildMenuUrl($item) }}" class="frontend-nav-trigger">
                            {{ $item['label'] }} <i class="fas fa-chevron-down frontend-nav-chevron" aria-hidden="true"></i>
                        </a>
                        <div class="frontend-nav-dropdown">
                            @foreach($children as $child)
                                @php
                                    $grandChildren = $child['children'] ?? [];
                                @endphp
                                <div class="frontend-nav-dropdown-row {{ $grandChildren ? 'has-children' : '' }}">
                                    <a href="{{ $buildMenuUrl($child) }}">
                                        <span>{{ $child['label'] }}</span>
                                        @if($grandChildren)
                                            <i class="fas fa-chevron-right frontend-nav-chevron" aria-hidden="true"></i>
                                        @endif
                                    </a>
                                    @if($grandChildren)
                                        <div class="frontend-nav-subdropdown">
                                            @foreach($grandChildren as $grandChild)
                                                <a href="{{ $buildMenuUrl($grandChild) }}">{{ $grandChild['label'] }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <a href="{{ $buildMenuUrl($item) }}" class="frontend-nav-link">{{ $item['label'] }}</a>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="frontend-nav-actions">
            @guest
                <button class="btn-ghost" onclick="window.openFrontendAuth('login')">Login</button>
                <a href="{{ $joinNowUrl }}" class="btn-primary" target="_blank" rel="noopener noreferrer">Join Now</a>
            @else
                <a href="{{ route('dashboard') }}" class="btn-primary">Dashboard</a>
            @endguest
        </div>

        <button class="frontend-nav-mobile-toggle" type="button" aria-label="Open menu" aria-expanded="false" data-frontend-nav-toggle>
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
    </div>

    <div class="frontend-nav-mobile-panel" data-frontend-nav-panel>
        @foreach($menuItems as $item)
            @php
                $children = $item['children'] ?? [];
            @endphp
            @if($children)
                <details class="frontend-nav-mobile-group">
                    <summary class="frontend-nav-mobile-summary">
                        <span>{{ $item['label'] }}</span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </summary>
                    <div class="frontend-nav-mobile-children">
                        <a href="{{ $buildMenuUrl($item) }}">All {{ $item['label'] }}</a>
                        @foreach($children as $child)
                            @php
                                $grandChildren = $child['children'] ?? [];
                            @endphp
                            <a href="{{ $buildMenuUrl($child) }}">{{ $child['label'] }}</a>
                            @if($grandChildren)
                                <div class="frontend-nav-mobile-grandchildren">
                                    @foreach($grandChildren as $grandChild)
                                        <a href="{{ $buildMenuUrl($grandChild) }}">{{ $grandChild['label'] }}</a>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                </details>
            @else
                <a href="{{ $buildMenuUrl($item) }}" class="frontend-nav-mobile-link">{{ $item['label'] }}</a>
            @endif
        @endforeach

        @guest
            <button class="frontend-nav-mobile-link" type="button" onclick="window.openFrontendAuth('login')">Login</button>
            <a href="{{ $joinNowUrl }}" class="frontend-nav-mobile-link" target="_blank" rel="noopener noreferrer">Join Now</a>
        @else
            <a href="{{ route('dashboard') }}" class="frontend-nav-mobile-link">Dashboard</a>
        @endguest
    </div>
</nav>

<script>
window.openFrontendAuth = window.openFrontendAuth || function (tab) {
    if (typeof window.openModal === 'function') {
        window.openModal(tab);
        return;
    }

    window.location.href = '{{ route('landing') }}?auth=' + encodeURIComponent(tab);
};
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-frontend-nav]').forEach(function (nav) {
        var toggle = nav.querySelector('[data-frontend-nav-toggle]');
        var panel = nav.querySelector('[data-frontend-nav-panel]');

        if (toggle && panel) {
            toggle.addEventListener('click', function () {
                var isOpen = panel.classList.toggle('open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        nav.querySelectorAll('.frontend-nav-dropdown-row.has-children').forEach(function (row) {
            var subdropdown = row.querySelector('.frontend-nav-subdropdown');
            if (!subdropdown) return;

            function fitSubdropdown() {
                var viewportPadding = 16;
                subdropdown.style.top = '-8px';
                subdropdown.style.maxHeight = 'calc(100vh - 32px)';

                window.requestAnimationFrame(function () {
                    var rect = subdropdown.getBoundingClientRect();
                    var topOffset = -8;
                    var bottomOverflow = rect.bottom - (window.innerHeight - viewportPadding);

                    if (bottomOverflow > 0) {
                        topOffset -= bottomOverflow;
                    }

                    if (rect.top + topOffset + 8 < viewportPadding) {
                        topOffset += viewportPadding - (rect.top + topOffset + 8);
                    }

                    subdropdown.style.top = topOffset + 'px';
                    subdropdown.style.maxHeight = Math.max(180, window.innerHeight - viewportPadding - subdropdown.getBoundingClientRect().top) + 'px';
                });
            }

            row.addEventListener('mouseenter', fitSubdropdown);
            row.addEventListener('focusin', fitSubdropdown);
        });
    });
});
</script>

