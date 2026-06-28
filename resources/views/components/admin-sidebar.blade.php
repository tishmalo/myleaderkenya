@php
    use Illuminate\Support\Facades\Route;

    $sidebar = config('backend.sidebar', []);
    $brand = $sidebar['brand'] ?? [];
    $overview = $sidebar['overview'] ?? null;
    $sections = $sidebar['sections'] ?? [];

    $isActive = function (array $item): bool {
        foreach ($item['active'] ?? [] as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return isset($item['route']) && $item['route'] && request()->routeIs($item['route']);
    };

    $itemHref = function (array $item): ?string {
        $route = $item['route'] ?? null;

        if (! $route || ! Route::has($route)) {
            return null;
        }

        return route($route);
    };
@endphp

<div class="w-72 bg-zinc-900 border-r border-zinc-800 flex flex-col">
    <div class="p-6 border-b border-zinc-800 flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl overflow-hidden flex-shrink-0">
            <img
                src="{{ asset($brand['logo'] ?? 'images/myleader.png') }}"
                alt="Tuko Kadi Logo"
                class="w-full h-full object-cover"
            >
        </div>
        <div>
            <h1 class="text-3xl font-semibold tracking-tighter">{{ $brand['name'] ?? 'ML KENYA' }}</h1>
            <p class="text-xs text-emerald-400 -mt-1">{{ $brand['tagline'] ?? 'THE KENYA. WE WANT' }}</p>
        </div>
    </div>

    <nav class="flex-1 p-6 overflow-y-auto">
        <ul class="space-y-2">
            @if($overview && ($href = $itemHref($overview)))
                <li>
                    <a href="{{ $href }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300 {{ $isActive($overview) ? 'active' : '' }}">
                        <i class="{{ $overview['icon'] ?? 'fas fa-circle' }} w-5"></i>
                        <span>{{ $overview['label'] }}</span>
                    </a>
                </li>
            @endif

            @foreach($sections as $section)
                <li class="mt-8">
                    <p class="px-5 text-xs font-medium text-zinc-500 uppercase tracking-widest mb-3">{{ $section['label'] }}</p>
                </li>

                @foreach($section['items'] ?? [] as $item)
                    @php($href = $itemHref($item))
                    <li>
                        @if($href)
                            <a href="{{ $href }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300 {{ $isActive($item) ? 'active' : '' }}">
                                <i class="{{ $item['icon'] ?? 'fas fa-circle' }} w-5"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @else
                            <span class="flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-600 cursor-not-allowed">
                                <i class="{{ $item['icon'] ?? 'fas fa-circle' }} w-5"></i>
                                <span>{{ $item['label'] }}</span>
                            </span>
                        @endif
                    </li>
                @endforeach
            @endforeach
        </ul>
    </nav>

    <div class="p-6 border-t border-zinc-800">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-500 rounded-2xl flex items-center justify-center text-white">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <p class="font-medium">{{ Auth::user()->name }}</p>
                <p class="text-xs text-zinc-500">Administrator</p>
            </div>
        </div>
    </div>
</div>
