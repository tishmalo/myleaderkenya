@extends('layouts.app')

@section('page_title', 'Positions Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-briefcase text-emerald-500"></i> 
            Positions Management
        </h1>
        
        <a href="{{ route('positions.create') }}" 
           class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i> Add New Position
        </a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-950">
                    <tr class="border-b border-zinc-800">
                        <th class="px-6 py-4 text-left">Position Name</th>
                        <th class="px-6 py-4 text-left">Sort</th>
                        <th class="px-6 py-4 text-left">Description</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse($positions as $position)
                        <tr class="hover:bg-zinc-800/70 transition-colors">
                            <td class="px-6 py-4 font-medium text-white">{{ $position->name }}</td>
                            <td class="px-6 py-4 text-zinc-400">{{ $position->sort_order }}</td>
                            <td class="px-6 py-4 text-zinc-400">{{ Str::limit($position->description, 120) }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-4 justify-center">
                                    <a href="{{ route('positions.edit', $position) }}" 
                                       class="text-blue-400 hover:text-blue-500">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deletePosition({{ $position->id }}, '{{ addslashes($position->name) }}')" 
                                            class="text-red-400 hover:text-red-500">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-zinc-500">
                                No positions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 flex justify-center">
        {{ $positions->appends(request()->query())->links() }}
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="bg-zinc-900 rounded-3xl p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold mb-4">Delete Position?</h3>
        <p id="deleteMessage" class="text-zinc-400 mb-8"></p>
        
        <div class="flex gap-4">
            <button onclick="hideDeleteModal()" class="flex-1 py-4 border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">Cancel</button>
            <button onclick="confirmDelete()" class="flex-1 bg-red-600 hover:bg-red-700 py-4 rounded-2xl font-medium">Yes, Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentDeleteId = null;

function deletePosition(id, name) {
    currentDeleteId = id;
    document.getElementById('deleteMessage').innerHTML = 
        `Are you sure you want to delete <strong>${name}</strong>?`;
    
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function confirmDelete() {
    if (!currentDeleteId) return;

    fetch(`/positions/${currentDeleteId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed to delete');
    })
    .catch(() => alert('Error occurred'));

    hideDeleteModal();
}
</script>
@endpush