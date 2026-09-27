@extends('layouts.app')

@section('page_title', 'Polling Stations')

@section('content')
<div class="max-w-7xl mx-auto">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-map-marker-alt text-emerald-500"></i> 
            Polling Stations
        </h1>
        
        <div class="flex gap-3">
            <button onclick="startAddForm()" 
                    class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl font-medium flex items-center gap-3 transition-all active:scale-95">
                <i class="fas fa-plus"></i> 
                Add New Station
            </button>

            <label class="bg-zinc-700 hover:bg-zinc-600 px-6 py-3 rounded-2xl font-medium flex items-center gap-3 transition-all cursor-pointer">
                <i class="fas fa-file-upload"></i> 
                Import JSON
                <input type="file" id="jsonImport" accept=".json" class="hidden" onchange="importJson(this)">
            </label>
        </div>
    </div>

    <!-- Updated Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="w-full max-w-full overflow-x-auto">
        <table class="min-w-[900px] w-full">
            <thead class="bg-zinc-950">
                <tr class="border-b border-zinc-800">
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Bloc / County</th>
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Constituency / Ward</th>
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Office</th>
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Registered Voters</th>
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Landmark</th>
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Added By</th>
                    <th class="px-8 py-5 text-right text-sm font-semibold text-zinc-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($stations ?? [] as $station)
                <tr class="hover:bg-zinc-800/70 transition-colors">
                    <td class="px-8 py-6">
                        <span class="font-medium">{{ $station->bloc?->name ?? '—' }}</span><br>
                        <span class="text-sm text-zinc-500">{{ $station->county }}</span>
                    </td>
                    <td class="px-8 py-6">
                        <span class="text-zinc-300">{{ $station->constituency }}</span><br>
                        <span class="text-sm text-zinc-500">{{ $station->ward ?? '—' }}</span>
                    </td>
                    <td class="px-8 py-6 text-zinc-300">{{ $station->office }}</td>
                    <td class="px-8 py-6">
                        <span class="font-medium text-emerald-400">{{ number_format($station->registered_voters ?? 0) }}</span>
                    </td>
                    <td class="px-8 py-6 text-zinc-400">{{ $station->near_landmark ?? '—' }}</td>
                    <td class="px-8 py-6">
                        @if($station->is_user_added)
                            <span class="px-4 py-1.5 bg-blue-500/10 text-blue-400 text-xs rounded-2xl">User Added</span>
                        @else
                            <span class="px-4 py-1.5 bg-emerald-500/10 text-emerald-400 text-xs rounded-2xl">Admin</span>
                        @endif
                    </td>
                    <td class="px-8 py-6">
                        @php
                            $stationPayload = [
                                'id'                => $station->id,
                                'bloc_id'           => $station->bloc_id,
                                'county'            => $station->county,
                                'constituency'      => $station->constituency,
                                'ward'              => $station->ward,
                                'office'            => $station->office,
                                'near_landmark'     => $station->near_landmark,
                                'lat'               => $station->lat,
                                'lon'               => $station->lon,
                                'registered_voters' => $station->registered_voters,
                            ];
                        @endphp
                        <div class="flex justify-end gap-2">
                            <button type="button"
                                    data-station='@json($stationPayload)'
                                    onclick="editStation(this)"
                                    class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-sm font-medium transition-colors">
                                <i class="fas fa-pen"></i> Edit
                            </button>
                            <button type="button"
                                    data-id="{{ $station->id }}"
                                    data-office="{{ $station->office }}"
                                    onclick="deleteStation(this)"
                                    class="px-4 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-sm font-medium transition-colors">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-8 py-20 text-center text-zinc-500">
                            No polling stations found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Add New Station Modal -->
<div id="addModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">
    <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-2xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-semibold mb-6">Add New Polling Station</h2>
        
        <form id="addStationForm">
            @csrf
            <input type="hidden" id="stationId" name="station_id" value="">

            <!-- Bloc -->
            <div class="mb-4">
                <label class="block text-sm text-zinc-400 mb-1">Bloc</label>
                <select id="bloc" name="bloc_id" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3">
                    <option value="">-- Select Bloc --</option>
                    @foreach($blocs ?? [] as $bloc)
                        <option value="{{ $bloc->id }}">{{ $bloc->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- County -->
            <div class="mb-4">
                <label class="block text-sm text-zinc-400 mb-1">County</label>
                <select id="county" name="county" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3">
                    <option value="">-- Select County --</option>
                </select>
            </div>

            <!-- Constituency -->
            <div class="mb-4">
                <label class="block text-sm text-zinc-400 mb-1">Constituency</label>
                <select id="constituency" name="constituency" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3">
                    <option value="">-- Select Constituency --</option>
                </select>
            </div>

            <!-- Ward -->
            <div class="mb-4">
                <label class="block text-sm text-zinc-400 mb-1">Ward</label>
                <select id="ward" name="ward" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3">
                    <option value="">-- Select Ward --</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-zinc-400 mb-1">Office / Polling Station Name</label>
                    <input type="text" name="office" required 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-1">Registered Voters</label>
                    <input type="number" name="registered_voters" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3" placeholder="0">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm text-zinc-400 mb-1">Near Landmark (optional)</label>
                <input type="text" name="near_landmark" 
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3">
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm text-zinc-400 mb-1">Latitude</label>
                    <input type="number" step="any" name="lat" required 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-1">Longitude</label>
                    <input type="number" step="any" name="lon" required 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3">
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" onclick="hideAddForm()" 
                        class="flex-1 py-4 border border-zinc-700 rounded-2xl font-medium">Cancel</button>
                <button type="submit" 
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium">Save Station</button>
            </div>        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Modal functions
    function showAddForm() {
        document.getElementById('addModal').classList.remove('hidden');
        document.getElementById('addModal').classList.add('flex');
    }

    function hideAddForm() {
        document.getElementById('addModal').classList.add('hidden');
        document.getElementById('addModal').classList.remove('flex');
    }

    // Opening the modal for a new station must clear any previous edit.
    function startAddForm() {
        const form = document.getElementById('addStationForm');
        form.reset();
        document.getElementById('stationId').value = '';

        fillSelect(countySelect, '-- Select County --', []);
        fillSelect(constituencySelect, '-- Select Constituency --', []);
        fillSelect(wardSelect, '-- Select Ward --', []);

        showAddForm();
    }
    // Form Submit - POST to create, PUT to update
    document.getElementById('addStationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        const stationId = formData.get('station_id');
        const isEdit = Boolean(stationId);

        const url = isEdit
            ? stationUrl(STATION_UPDATE_URL, stationId)
            : '{{ route("stations.store") }}';

        try {
            const response = await fetch(url, {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                alert(isEdit ? '✅ Polling station updated successfully!' : '✅ Polling station added successfully!');
                hideAddForm();
                location.reload();
            } else {
                const errors = result.errors
                    ? Object.values(result.errors).flat().join('\n')
                    : null;
                alert('❌ Error: ' + (errors || result.message || (isEdit ? 'Failed to update station' : 'Failed to add station')));
            }
        } catch (error) {
            console.error(error);
            alert('Request failed. Check console for details.');
        }
    });

    // Cascading Dropdowns (each endpoint returns a flat array of names)
    const blocSelect         = document.getElementById('bloc');
    const countySelect       = document.getElementById('county');
    const constituencySelect = document.getElementById('constituency');
    const wardSelect         = document.getElementById('ward');

    const STATION_UPDATE_URL   = '{{ route('stations.update', ['station' => '__STATION__']) }}';
    const STATION_DESTROY_URL  = '{{ route('stations.destroy', ['station' => '__STATION__']) }}';

    function stationUrl(template, stationId) {
        return template.replace('__STATION__', encodeURIComponent(stationId));
    }

    async function fetchNameList(url, label) {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

        if (!res.ok) {
            throw new Error(`${label} request failed (HTTP ${res.status})`);
        }

        const data = await res.json();

        return Array.isArray(data) ? data : [];
    }

    function fillSelect(select, placeholder, values, selected = '') {
        select.innerHTML = `<option value="">${placeholder}</option>`;

        const names = Array.isArray(values) ? [...values] : [];

        // Keep a stale value selectable so editing an existing station is not
        // blocked by a county/constituency/ward that is no longer linked.
        if (selected && !names.includes(selected)) {
            names.push(selected);
        }

        names.forEach(name => select.appendChild(new Option(name, name)));

        select.value = selected;
    }

    blocSelect.addEventListener('change', async function() {
        const blocId = this.value;

        fillSelect(countySelect, '-- Select County --', []);
        fillSelect(constituencySelect, '-- Select Constituency --', []);
        fillSelect(wardSelect, '-- Select Ward --', []);

        if (!blocId) return;

        try {
            fillSelect(
                countySelect,
                '-- Select County --',
                await fetchNameList(`/api/counties/by-bloc/${blocId}`, 'Counties')
            );
        } catch (err) {
            console.error('Error fetching counties:', err);
            alert('❌ Could not load counties for this bloc.');
        }
    });

    countySelect.addEventListener('change', async function() {
        const county = this.value;

        fillSelect(constituencySelect, '-- Select Constituency --', []);
        fillSelect(wardSelect, '-- Select Ward --', []);

        if (!county) return;

        try {
            fillSelect(
                constituencySelect,
                '-- Select Constituency --',
                await fetchNameList(
                    `/api/constituencies/by-county?county=${encodeURIComponent(county)}`,
                    'Constituencies'
                )
            );
        } catch (err) {
            console.error('Error fetching constituencies:', err);
            alert('❌ Could not load constituencies for this county.');
        }
    });

    constituencySelect.addEventListener('change', async function() {
        const constituency = this.value;

        fillSelect(wardSelect, '-- Select Ward --', []);

        if (!constituency) return;

        try {
            fillSelect(
                wardSelect,
                '-- Select Ward --',
                await fetchNameList(
                    `/api/wards/by-constituency?constituency=${encodeURIComponent(constituency)}`,
                    'Wards'
                )
            );
        } catch (err) {
            console.error('Error fetching wards:', err);
            alert('❌ Could not load wards for this constituency.');
        }
    });

    // Edit: pre-fill the shared modal, then load each cascade level.
    async function editStation(button) {
        const station = JSON.parse(button.dataset.station);

        const form = document.getElementById('addStationForm');
        form.reset();

        document.getElementById('stationId').value = station.id ?? '';
        blocSelect.value = station.bloc_id ?? '';

        form.querySelector('[name="office"]').value            = station.office ?? '';
        form.querySelector('[name="near_landmark"]').value     = station.near_landmark ?? '';
        form.querySelector('[name="lat"]').value               = station.lat ?? '';
        form.querySelector('[name="lon"]').value               = station.lon ?? '';
        form.querySelector('[name="registered_voters"]').value = station.registered_voters ?? 0;

        const county = station.county ?? '';

        if (blocSelect.value) {
            try {
                fillSelect(
                    countySelect,
                    '-- Select County --',
                    await fetchNameList(`/api/counties/by-bloc/${blocSelect.value}`, 'Counties'),
                    county
                );
            } catch (err) {
                console.error('Error fetching counties:', err);
                fillSelect(countySelect, '-- Select County --', [], county);
            }
        } else {
            fillSelect(countySelect, '-- Select County --', [], county);
        }

        if (county) {
            try {
                fillSelect(
                    constituencySelect,
                    '-- Select Constituency --',
                    await fetchNameList(
                        `/api/constituencies/by-county?county=${encodeURIComponent(county)}`,
                        'Constituencies'
                    ),
                    station.constituency ?? ''
                );
            } catch (err) {
                console.error('Error fetching constituencies:', err);
                fillSelect(constituencySelect, '-- Select Constituency --', [], station.constituency ?? '');
            }
        } else {
            fillSelect(constituencySelect, '-- Select Constituency --', []);
        }

        if (station.constituency) {
            try {
                fillSelect(
                    wardSelect,
                    '-- Select Ward --',
                    await fetchNameList(
                        `/api/wards/by-constituency?constituency=${encodeURIComponent(station.constituency)}`,
                        'Wards'
                    ),
                    station.ward ?? ''
                );
            } catch (err) {
                console.error('Error fetching wards:', err);
                fillSelect(wardSelect, '-- Select Ward --', [], station.ward ?? '');
            }
        } else {
            fillSelect(wardSelect, '-- Select Ward --', []);
        }

        showAddForm();
    }

    async function deleteStation(button) {
        const stationId = button.dataset.id;
        const office    = button.dataset.office;

        if (!confirm(`Delete the polling station "${office}"? This cannot be undone.`)) {
            return;
        }

        try {
            const response = await fetch(stationUrl(STATION_DESTROY_URL, stationId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok) {
                alert('✅ Polling station deleted successfully!');
                location.reload();
            } else {
                alert('❌ Error: ' + (result.message || 'Failed to delete station'));
            }
        } catch (error) {
            console.error(error);
            alert('Request failed. Check console for details.');
        }
    }

    // Keep your importJson function if you still need it
    async function importJson(input) {
        // ... your existing import logic
    }
</script>
@endpush
