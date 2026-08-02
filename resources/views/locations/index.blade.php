@extends('layouts.app')

@section('page_title', 'Voter Locations')

@section('content')
<div class="max-w-7xl mx-auto">

    <h1 class="text-3xl font-semibold mb-8 flex items-center gap-3 text-white">
        <i class="fas fa-map text-emerald-500"></i>
        Voter Locations Dashboard
    </h1>

    <!-- Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden mb-10">
        <div class="w-full max-w-full overflow-x-auto">
        <table class="min-w-[900px] w-full">
            <thead class="bg-zinc-950">
                <tr class="border-b border-zinc-800">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Latitude</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Longitude</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($locations ?? [] as $loc)
                    <tr class="hover:bg-zinc-800/70 transition-colors">
                        <td class="px-6 py-4 font-medium text-white">{{ $loc->name }}</td>
                        <td class="px-6 py-4 text-zinc-400 font-mono">{{ $loc->latitude ?? '-' }}</td>
                        <td class="px-6 py-4 text-zinc-400 font-mono">{{ $loc->longitude ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-16 text-center text-zinc-500">
                            No locations found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if(method_exists($locations, 'links'))
            <div class="border-t border-zinc-800 px-6 py-4">
                {{ $locations->links() }}
            </div>
        @endif
    </div>

    <!-- Interactive Map -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
        <h2 class="text-xl font-semibold mb-6 flex items-center gap-3 text-white">
            <i class="fas fa-map-marked-alt text-emerald-500"></i>
            Map View
        </h2>
        <div id="map" style="height: 600px; width: 100%; border-radius: 16px; overflow: hidden;"></div>
    </div>

</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const map = L.map('map').setView([0, 0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const locations = @json($mapLocations ?? []);

        if (locations.length > 0) {
            const bounds = L.latLngBounds(
                locations.map(loc => [loc.latitude, loc.longitude])
            );

            locations.forEach(loc => {
                L.marker([loc.latitude, loc.longitude])
                    .addTo(map)
                    .bindPopup(`<b>${loc.name}</b><br>Lat: ${loc.latitude}<br>Lng: ${loc.longitude}`);
            });

            map.fitBounds(bounds, { padding: [50, 50] });
        } else {
            map.setView([20, 0], 2);
        }

        map.getContainer().style.filter = 'brightness(0.85) contrast(1.1) saturate(0.8)';
    });
</script>
@endpush

@endsection

