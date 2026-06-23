@extends('layouts.app')

@section('page_title', 'Constituencies')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3">
            <i class="fas fa-map-marker-alt"></i> Constituencies
        </h1>
        <div class="flex gap-3">
            <a href="{{ route('constituencies.create') }}" class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl font-medium flex items-center gap-2">
                <i class="fas fa-plus"></i> Add Constituency
            </a>
            <label class="cursor-pointer bg-zinc-700 hover:bg-zinc-600 px-6 py-3 rounded-2xl font-medium flex items-center gap-2">
                <i class="fas fa-file-upload"></i> Import JSON
                <input type="file" id="constituencyImport" accept=".json" class="hidden" onchange="importConstituencies(this)">
            </label>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 mb-8">
        <form method="GET" id="searchForm" class="flex gap-4">
            <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                   placeholder="Search constituency name..."
                   class="flex-1 bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 focus:outline-none focus:border-emerald-500">
            <a href="{{ route('constituencies.index') }}" class="bg-zinc-800 hover:bg-zinc-700 px-8 rounded-2xl font-medium flex items-center">Clear</a>
        </form>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr class="border-b border-zinc-800">
                    <th class="px-8 py-5 text-left">Constituency Name</th>
                    <th class="px-8 py-5 text-left">County</th>
                    <th class="px-8 py-5 text-left">Bloc</th>
                    <th class="px-8 py-5 text-center">Population</th>
                    <th class="px-8 py-5 text-center">Polling Stations</th>
                    <th class="px-8 py-5 text-center">Registered Voters</th>
                    <th class="px-8 py-5 text-center">Seats</th>
                    <th class="px-8 py-5 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($constituencies as $constituency)
                <tr class="hover:bg-zinc-800/70 transition-colors">
                    <td class="px-8 py-6 font-medium">{{ $constituency->name }}</td>
                    <td class="px-8 py-6">{{ $constituency->county->name ?? '—' }}</td>
                    <td class="px-8 py-6">{{ $constituency->county->bloc->name ?? '—' }}</td>
                    <td class="px-8 py-6 text-center">{{ number_format($constituency->population ?? 0) }}</td>
                    
                    <td class="px-8 py-6 text-center">
                        <button onclick="showPollingStations({{ $constituency->id }}, 'constituency')" 
                                class="text-emerald-400 hover:text-emerald-500 font-medium underline">
                            {{ $constituency->polling_stations_count ?? 0 }}
                        </button>
                    </td>
                    <td class="px-8 py-6 text-center font-medium text-emerald-400">
                        {{ number_format($constituency->registered_voters ?? 0) }}
                    </td>

                    <td class="px-8 py-6 text-center font-medium">{{ $constituency->number_of_seats }}</td>
                    <td class="px-8 py-6 text-center">
                        <a href="{{ route('constituencies.edit', $constituency) }}" class="text-emerald-400 hover:text-emerald-500 mr-4">Edit</a>
                        <button onclick="showDeleteModal('{{ route('constituencies.destroy', $constituency) }}', 'Delete this constituency?')" 
                                class="text-red-400 hover:text-red-500">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-8 py-20 text-center text-zinc-500">No constituencies found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-center">
        {{ $constituencies->appends(request()->query())->links() }}
    </div>
</div>

<!-- Same Modal as above (copy from Counties view) -->
<div id="pollingModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-[9999]">
    <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-4xl mx-4 p-8 max-h-[85vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold" id="modalTitle">Polling Stations</h2>
            <button onclick="hidePollingModal()" class="text-zinc-400 hover:text-white text-3xl leading-none">×</button>
        </div>
        <div class="overflow-auto flex-1" id="modalContent"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Live search as you type
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('searchForm').submit();
    }, 500); // Wait 500ms after user stops typing
});

function showPollingStations(id, type) {
    const modal = document.getElementById('pollingModal');
    const content = document.getElementById('modalContent');
    const title = document.getElementById('modalTitle');

    title.textContent = `Polling Stations in ${type.charAt(0).toUpperCase() + type.slice(1)}`;
    content.innerHTML = `<div class="text-center py-12"><i class="fas fa-spinner fa-spin text-4xl text-emerald-500"></i><p class="mt-4 text-zinc-400">Loading...</p></div>`;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    fetch(`/api/polling-stations/${type}/${id}`)
        .then(res => res.json())
        .then(data => {
            let html = `<table class="w-full"><thead class="bg-zinc-950 sticky top-0"><tr><th class="px-6 py-4 text-left">Office</th><th class="px-6 py-4 text-left">Ward</th><th class="px-6 py-4 text-center">Registered Voters</th></tr></thead><tbody class="divide-y divide-zinc-800">`;
            
            if (data.length === 0) {
                html += `<tr><td colspan="3" class="px-6 py-12 text-center text-zinc-500">No polling stations found.</td></tr>`;
            } else {
                data.forEach(station => {
                    html += `<tr class="hover:bg-zinc-800"><td class="px-6 py-4">${station.office}</td><td class="px-6 py-4">${station.ward || '—'}</td><td class="px-6 py-4 text-center font-medium text-emerald-400">${Number(station.registered_voters || 0).toLocaleString()}</td></tr>`;
                });
            }
            html += `</tbody></table>`;
            content.innerHTML = html;
        })
        .catch(() => content.innerHTML = `<p class="text-red-400 text-center py-8">Failed to load data.</p>`);
}

function hidePollingModal() {
    document.getElementById('pollingModal').classList.add('hidden').classList.remove('flex');
}
</script>
@endpush