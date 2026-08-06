@php
    use Illuminate\Support\Facades\Route;

    $sidebar = config('my-account-sidebar', []);
    $items = $sidebar['items'] ?? [];
    $footer = $sidebar['footer'] ?? [];

    $itemUrl = function (array $item): string {
        if (! empty($item['route']) && Route::has($item['route'])) {
            return route($item['route'], $item['params'] ?? []);
        }

        return $item['href'] ?? '#';
    };

    $itemActive = function (array $item): bool {
        foreach ($item['active'] ?? [] as $pattern) {
            if (request()->routeIs($pattern)) return true;
        }

        return false;
    };
@endphp

<aside class="account-sidebar" aria-label="My Account navigation">
    <a href="{{ route('my-account') }}" class="account-sidebar-brand">
        <span class="account-sidebar-logo"><i class="{{ $sidebar['brand']['icon'] ?? 'fas fa-user-circle' }}"></i></span>
        <span><small>{{ $sidebar['brand']['label'] ?? 'My Leader Kenya' }}</small><strong>{{ $sidebar['brand']['title'] ?? 'My Account' }}</strong></span>
    </a>

    <nav class="account-sidebar-nav">
        @foreach($items as $item)
            <a href="{{ $itemUrl($item) }}" @if(! empty($item['target'])) target="{{ $item['target'] }}" @endif @if(! empty($item['rel'])) rel="{{ $item['rel'] }}" @endif class="account-sidebar-link {{ $itemActive($item) ? 'is-active' : '' }} {{ ($item['style'] ?? '') === 'primary' ? 'is-primary' : '' }}">
                <i class="{{ $item['icon'] ?? 'fas fa-circle' }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    @if($footer && Route::has($footer['route'] ?? ''))
        <form method="POST" action="{{ route($footer['route']) }}" class="account-sidebar-footer">
            @csrf
            <button type="submit" class="account-sidebar-logout"><i class="{{ $footer['icon'] ?? 'fas fa-sign-out-alt' }}"></i><span>{{ $footer['label'] ?? 'Logout' }}</span></button>
        </form>
    @endif
</aside>