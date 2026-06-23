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
            <button onclick="showAddForm()" 
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
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr class="border-b border-zinc-800">
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Bloc / County</th>
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Constituency / Ward</th>
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Office</th>
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Registered Voters</th>
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Landmark</th>
                    <th class="px-8 py-5 text-left text-sm font-semibold text-zinc-400">Added By</th>
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
                </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-8 py-20 text-center text-zinc-500">
                            No polling stations found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add New Station Modal -->
<div id="addModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">
    <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-2xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-semibold mb-6">Add New Polling Station</h2>
        
        <form id="addStationForm">
            @csrf

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
            </div>
        </form>
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

    // Form Submit
    document.getElementById('addStationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('{{ route("stations.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                alert('✅ Polling station added successfully!');
                hideAddForm();
                location.reload();
            } else {
                alert('❌ Error: ' + (result.message || 'Failed to add station'));
            }
        } catch (error) {
            console.error(error);
            alert('Request failed. Check console for details.');
        }
    });

    // Cascading Dropdowns - Fixed Version (expects array of strings)
    const blocSelect         = document.getElementById('bloc');
    const countySelect       = document.getElementById('county');
    const constituencySelect = document.getElementById('constituency');
    const wardSelect         = document.getElementById('ward');

    blocSelect.addEventListener('change', async function() {
        const blocId = this.value;
        
        countySelect.innerHTML = '<option value="">-- Select County --</option>';
        constituencySelect.innerHTML = '<option value="">-- Select Constituency --</option>';
        wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

        if (!blocId) return;

        try {
            const res = await fetch(`/api/counties/by-bloc/${blocId}`);
            const counties = await res.json();   // should be ["County Name 1", "County Name 2", ...]

            counties.forEach(name => {
                const opt = new Option(name, name);
                countySelect.appendChild(opt);
            });
        } catch (err) {
            console.error('Error fetching counties:', err);
        }
    });

    countySelect.addEventListener('change', async function() {
        const county = this.value;
        
        constituencySelect.innerHTML = '<option value="">-- Select Constituency --</option>';
        wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

        if (!county) return;

        try {
            const res = await fetch(`/api/constituencies/by-county?county=${encodeURIComponent(county)}`);
            const constituencies = await res.json();

            constituencies.forEach(name => {
                const opt = new Option(name, name);
                constituencySelect.appendChild(opt);
            });
        } catch (err) {
            console.error('Error fetching constituencies:', err);
        }
    });

    constituencySelect.addEventListener('change', async function() {
        const constituency = this.value;
        
        wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

        if (!constituency) return;

        try {
            const res = await fetch(`/api/wards/by-constituency?constituency=${encodeURIComponent(constituency)}`);
            const wards = await res.json();

            wards.forEach(name => {
                const opt = new Option(name, name);
                wardSelect.appendChild(opt);
            });
        } catch (err) {
            console.error('Error fetching wards:', err);
        }
    });

    // Keep your importJson function if you still need it
    async function importJson(input) {
        // ... your existing import logic
    }
</script>
@endpush