<style data-audit-sidebar-styles>
.asp-sidebar{position:sticky;top:18px;width:280px;max-height:calc(100vh - 36px);overflow:auto;border:1px solid rgba(255,255,255,.09);border-radius:8px;background:#101010;padding:18px;display:flex;flex-direction:column;flex:0 0 280px}.asp-sidebar-brand{border-bottom:1px solid rgba(255,255,255,.08);padding-bottom:16px;margin-bottom:14px}.asp-sidebar-brand span{display:block;color:#00A86B;font-size:11px;font-weight:900;letter-spacing:.16em;text-transform:uppercase}.asp-sidebar-brand strong{display:block;margin-top:4px;color:#fff;font-size:25px;line-height:1}.asp-sidebar-nav{display:grid;gap:7px;flex:1}.asp-sidebar-link{display:flex;align-items:center;gap:11px;min-height:42px;padding:0 12px;border:1px solid transparent;border-radius:8px;color:rgba(245,245,240,.66);text-decoration:none;font-weight:800;font-size:13px}.asp-sidebar-link i{width:18px;color:#00A86B;text-align:center}.asp-sidebar-link:hover,.asp-sidebar-link.active{color:#fff;background:#171717;border-color:rgba(0,168,107,.26)}.asp-sidebar-top{margin:0 0 14px;padding-bottom:14px;border-bottom:1px solid rgba(255,255,255,.08)}.asp-sidebar-logout{width:100%;display:flex;align-items:center;gap:11px;min-height:42px;padding:0 12px;border:1px solid rgba(239,68,68,.22);border-radius:8px;background:rgba(239,68,68,.08);color:#ffb4b4;font:inherit;font-size:13px;font-weight:900;cursor:pointer}@media(max-width:900px){.asp-sidebar{position:static;width:100%;max-height:none;flex-basis:auto}.asp-sidebar-nav{display:flex;overflow-x:auto}.asp-sidebar-link{flex:0 0 auto}}
</style>
@php
    use Illuminate\Support\Facades\Route;

    $sidebar = config('aspirant-sidebar', []);
    $items = $sidebar['items'] ?? [];
    $isDashboard = request()->routeIs('aspirant.dashboard');

    $itemHref = function (array $item): string {
        // Dashboard sections must always use an absolute dashboard URL. This
        // deliberately does not depend on refreshed config cache, because a
        // stale cached href such as "#analytics" would target the current page.
        if (! empty($item['section']) && Route::has('aspirant.dashboard')) {
            return route('aspirant.dashboard').'#'.ltrim($item['section'], '#');
        }

        if (! empty($item['route']) && Route::has($item['route'])) {
            $url = route($item['route'], $item['params'] ?? []);

            if (! empty($item['fragment'])) {
                $url .= '#'.ltrim($item['fragment'], '#');
            }

            return $url;
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

<aside class="asp-sidebar" aria-label="Aspirant dashboard navigation" data-aspirant-sidebar>
    <a href="{{ route('my-account') }}" class="asp-sidebar-link mb-3"><i class="fas fa-arrow-left"></i><span>Back to My Account</span></a>
    <div class="asp-sidebar-brand">
        <span>{{ $sidebar['brand']['label'] ?? 'Aspirant' }}</span>
        <strong>{{ $sidebar['brand']['title'] ?? 'Dashboard' }}</strong>
    </div>

    <div class="asp-sidebar-top">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="asp-sidebar-logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
    <nav class="asp-sidebar-nav" data-aspirant-sidebar-nav>
        @foreach($items as $item)
            @php
                $href = $itemHref($item);
                $active = $isActive($item);
            @endphp
            <a
                href="{{ $href }}"
                class="asp-sidebar-link {{ $active ? 'active' : '' }}"
                @if($isDashboard && ! empty($item['section'])) data-dashboard-section-link="{{ $item['section'] }}" @endif
            >
                <i class="{{ $item['icon'] ?? 'fas fa-circle' }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

</aside>

<script>
(() => {
    const sidebar = document.querySelector('[data-aspirant-sidebar]');
    const navigation = sidebar?.querySelector('[data-aspirant-sidebar-nav]');

    if (!sidebar || !navigation) return;

    const storageKey = 'mlk.aspirant-sidebar-position.v1';
    const readPosition = () => {
        try {
            const value = JSON.parse(sessionStorage.getItem(storageKey) || '{}');
            return {
                top: Number.isFinite(Number(value.top)) ? Math.max(0, Number(value.top)) : 0,
                left: Number.isFinite(Number(value.left)) ? Math.max(0, Number(value.left)) : 0,
            };
        } catch (error) {
            return { top: 0, left: 0 };
        }
    };
    const savePosition = () => {
        try {
            sessionStorage.setItem(storageKey, JSON.stringify({
                top: sidebar.scrollTop,
                left: navigation.scrollLeft,
            }));
        } catch (error) {
            // Navigation remains fully usable when browser storage is unavailable.
        }
    };

    const position = readPosition();
    sidebar.scrollTop = position.top;
    navigation.scrollLeft = position.left;

    sidebar.addEventListener('scroll', savePosition, { passive: true });
    navigation.addEventListener('scroll', savePosition, { passive: true });
    sidebar.addEventListener('click', event => {
        if (event.target.closest('a, button')) savePosition();
    });
})();
</script>
