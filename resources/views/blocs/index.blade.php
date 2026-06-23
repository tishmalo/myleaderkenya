@extends('layouts.app')

@section('page_title', 'Blocs')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold text-white">Blocs</h1>
        <div class="flex gap-3">
            <a href="{{ route('blocs.create') }}" class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl font-medium flex items-center gap-2">
                <i class="fas fa-plus"></i> Add Bloc
            </a>
            <label class="cursor-pointer bg-zinc-700 hover:bg-zinc-600 px-6 py-3 rounded-2xl font-medium flex items-center gap-2">
                <i class="fas fa-file-upload"></i> Import JSON
                <input type="file" id="blocImport" accept=".json" class="hidden" onchange="importBlocs(this)">
            </label>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 mb-8">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search bloc name..." 
                   class="flex-1 bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3">
            <button type="submit" class="bg-zinc-800 hover:bg-zinc-700 px-8 rounded-2xl font-medium">Search</button>
            <a href="{{ route('blocs.index') }}" class="bg-zinc-800 hover:bg-zinc-700 px-8 rounded-2xl font-medium flex items-center">Clear</a>
        </form>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr>
                    <th class="px-8 py-5 text-left">Name</th>
                    <th class="px-8 py-5 text-left">Tribes</th>
                    <th class="px-8 py-5 text-center">Population</th>
                    <th class="px-8 py-5 text-center">Counties</th>
                    <th class="px-8 py-5 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($blocs as $bloc)
                <tr class="hover:bg-zinc-800/70">
                    <td class="px-8 py-6 font-semibold">{{ $bloc->name }}</td>
                    <td class="px-8 py-6">{{ implode(', ', $bloc->tribes ?? []) }}</td>
                    <td class="px-8 py-6 text-center">{{ number_format($bloc->tribe_population ?? 0) }}</td>
                    <td class="px-8 py-6 text-center">{{ $bloc->counties_count }}</td>
                    <td class="px-8 py-6 text-center">
                        <a href="{{ route('blocs.edit', $bloc) }}" class="text-emerald-400 hover:text-emerald-500 mr-4">Edit</a>
                        <button onclick="showDeleteModal('{{ route('blocs.destroy', $bloc) }}', 'Delete this bloc and all related counties?')" 
                                class="text-red-400 hover:text-red-500">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-8 py-20 text-center text-zinc-500">No blocs found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $blocs->appends(request()->query())->links() }}
    </div>
</div>
@endsection

<script>
async function importBlocs(input) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = async function(e) {
        try {
            let data = JSON.parse(e.target.result);
            const blocs = Array.isArray(data) ? data : Object.values(data);

            const response = await fetch('{{ route("blocs.import") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ blocs: blocs })
            });

            const result = await response.json();
            if (response.ok) {
                alert(`✅ Imported ${result.imported} blocs successfully!`);
                location.reload();
            } else {
                alert('❌ Import failed: ' + (result.message || 'Unknown error'));
            }
        } catch (err) {
            alert('❌ Invalid JSON file');
        }
    };
    reader.readAsText(file);
    input.value = '';
}
</script>

