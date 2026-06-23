@extends('layouts.app')

@section('page_title', 'Edit Position')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3">
            <i class="fas fa-user-edit text-emerald-500"></i>
            Edit Position
        </h1>
        <a href="{{ route('positions.index') }}" 
           class="text-zinc-400 hover:text-white flex items-center gap-2">
            ← Back to Positions
        </a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('positions.update', $position) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Position Name -->
            <div>
                <label class="block text-sm text-zinc-400 mb-2">
                    Position Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       value="{{ old('name', $position->name) }}"
                       required
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                       placeholder="e.g. Member of Parliament">
            </div>

            <!-- Description -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Description</label>
                <textarea name="description" 
                          rows="5"
                          class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500 resize-y min-h-[120px]"
                          placeholder="Brief description of the position...">{{ old('description', $position->description) }}</textarea>
            </div>

            <!-- Action Buttons -->
            <div class="mt-10 flex gap-4">
                <a href="{{ route('positions.index') }}" 
                   class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold transition-colors">
                    Update Position
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentDeleteId = null;
let currentDeleteUrl = null;

function deletePosition(id, name) {
    currentDeleteId = id;
    currentDeleteUrl = `/positions/${id}`;

    document.getElementById('deleteMessage').innerHTML = 
        `Are you sure you want to delete <strong>${name}</strong>?<br><br>This action cannot be undone.`;
    
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
    if (!currentDeleteUrl) return;

    fetch(currentDeleteUrl, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideDeleteModal();
            // Show success message
            alert(data.message || 'Item deleted successfully!');
            // Refresh the page to show updated list
            setTimeout(() => {
                location.reload();
            }, 300);
        } else {
            alert(data.message || 'Failed to delete item');
            hideDeleteModal();
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('An error occurred while deleting. Please try again.');
        hideDeleteModal();
    });
}
</script>
@endpush