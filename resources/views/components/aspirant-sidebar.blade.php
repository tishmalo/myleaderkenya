@php
    use Illuminate\Support\Facades\Route;

    $sidebar = config('aspirant-sidebar', []);
    $items = $sidebar['items'] ?? [];

    $itemHref = function (array $item): string {
        if (! empty($item['route']) && Route::has($item['route'])) {
            return route($item['route'], $item['params'] ?? []);
        }

        return $item['href'] ?? '#analytics';
    };

    $isActive = function (array $item): bool {
        foreach ($item['active'] ?? [] as $pattern) {
            if (! request()->routeIs($pattern)) {
                continue;
            }

            $toolKey = $item['tool_key'] ?? null;

            if (! $toolKey || request()->route('key') === $toolKey) {
                return true;
            }
        }

        return ($item['section'] ?? null) === 'analytics' && request()->routeIs('aspirant.dashboard');
    };
@endphp

<aside class="asp-sidebar" aria-label="Aspirant dashboard navigation">
    <div class="asp-sidebar-brand">
        <span>{{ $sidebar['brand']['label'] ?? 'Aspirant' }}</span>
        <strong>{{ $sidebar['brand']['title'] ?? 'Dashboard' }}</strong>
    </div>

    <nav class="asp-sidebar-nav">
        @foreach($items as $item)
            @php
                $href = $itemHref($item);
                $active = $isActive($item);
            @endphp
            <a
                href="{{ $href }}"
                class="asp-sidebar-link {{ $active ? 'active' : '' }}"
                @if(! empty($item['section'])) data-dashboard-section-link="{{ $item['section'] }}" @endif
            >
                <i class="{{ $item['icon'] ?? 'fas fa-circle' }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>
