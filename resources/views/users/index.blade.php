@extends('layouts.app')

@section('page_title', 'Voters Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-user-check text-emerald-500"></i> 
            Voters Management
        </h1>
        
        <a href="{{ route('users.create') }}" 
           class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i> Add New User
        </a>
    </div>

    <!-- Search & Filter Bar (optional - keep if you want) -->
    <!-- You can add your search/filter here later -->

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px]"> <!-- min-width ensures horizontal scroll -->
                <thead class="bg-zinc-950 sticky top-0 z-10">
                    <tr class="border-b border-zinc-800">
                        <th class="px-6 py-4 text-left">Username</th>
                        <th class="px-6 py-4 text-left">Full Name</th>
                        <th class="px-6 py-4 text-left">Phone</th>
                        <th class="px-6 py-4 text-left">Email</th>
                        <th class="px-6 py-4 text-left">Gender</th>
                        <th class="px-6 py-4 text-center">Year of Birth</th>
                        <th class="px-6 py-4 text-left">County</th>
                        <th class="px-6 py-4 text-left">Constituency</th>
                        <th class="px-6 py-4 text-left">Ward</th>
                        <th class="px-6 py-4 text-left">Polling Station</th>
                        <th class="px-6 py-4 text-left">Country</th>
                        <th class="px-6 py-4 text-center">Voter Status</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse($users as $user)
                        @if($user->email !== 'admin@tukokadi.com')
                            <tr class="hover:bg-zinc-800/70 transition-colors">
                                <td class="px-6 py-4 font-medium text-white">{{ $user->username }}</td>
                                <td class="px-6 py-4">{{ $user->name }}</td>
                                <td class="px-6 py-4">{{ $user->phone ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $user->email ?? '—' }}</td>
                                <td class="px-6 py-4">{{ ucfirst($user->gender ?? '—') }}</td>
                                <td class="px-6 py-4 text-center">{{ $user->year_of_birth ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $user->county ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $user->constituency ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $user->ward ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $user->polling_station ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $user->country_of_residence ?? 'Kenya' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        {{ ($user->is_voter == 1 || $user->is_registered == 1) 
                                            ? 'bg-emerald-500/20 text-emerald-400' 
                                            : 'bg-orange-500/20 text-orange-400' }}">
                                        {{ ($user->is_voter == 1 || $user->is_registered == 1) ? 'Registered' : 'Not Registered' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex gap-3 justify-center">
                                        <a href="{{ route('users.edit', $user) }}" 
                                           class="text-blue-400 hover:text-blue-500 transition-colors">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')" 
                                                class="text-red-400 hover:text-red-500 transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="13" class="px-6 py-16 text-center text-zinc-500">
                                No voters found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-8 flex justify-center">
        {{ $users->appends(request()->query())->links() }}
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="bg-zinc-900 rounded-3xl p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold mb-4">Delete User?</h3>
        <p id="deleteMessage" class="text-zinc-400 mb-8"></p>
        
        <div class="flex gap-4">
            <button onclick="hideDeleteModal()" 
                    class="flex-1 py-4 border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">
                Cancel
            </button>
            <button onclick="confirmDelete()" 
                    class="flex-1 bg-red-600 hover:bg-red-700 py-4 rounded-2xl font-medium">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentDeleteId = null;

function deleteUser(id, name) {
    currentDeleteId = id;
    document.getElementById('deleteMessage').innerHTML = 
        `Are you sure you want to permanently delete <strong>${name}</strong>?<br><br>This action cannot be undone.`;
    
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

    hideDeleteModal();

    fetch(`/users/${currentDeleteId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();   // Refresh to update the list
        } else {
            alert(data.message || 'Failed to delete user');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('An error occurred while deleting the user');
    });
}
</script>
@endpush