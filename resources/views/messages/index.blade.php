@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-comment-dots text-emerald-500"></i> 
            Messages & Discussions
        </h1>
        
        <div class="flex gap-3">
            <a href="{{ route('groups.create') }}" 
               class="bg-violet-600 hover:bg-violet-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2 transition-all">
                <i class="fas fa-plus"></i> Create Group
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex border-b border-zinc-800 mb-8">
        <button onclick="switchTab(0)" 
                id="tab-counties"
                class="tab-button active px-8 py-4 font-medium text-lg border-b-2 border-emerald-500 text-emerald-400 flex items-center gap-2">
            🗳️ County & Constituency Chats
        </button>
        
        <button onclick="switchTab(1)" 
                id="tab-groups"
                class="tab-button px-8 py-4 font-medium text-lg text-zinc-400 hover:text-white transition-colors flex items-center gap-2">
            👥 My Groups
        </button>
    </div>

    <!-- ==================== COUNTY / CONSTITUENCY SECTION ==================== -->
<div id="content-counties" class="tab-content">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-zinc-800 bg-zinc-950 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-globe"></i> 
                Public Constituency Messages
            </h2>
            <!-- <a href="#" 
   onclick="alert('Public messages are sent through the mobile app.\n\nYou can view them here on the dashboard.')"
   class="bg-emerald-600 hover:bg-emerald-700 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
    <i class="fas fa-paper-plane"></i> Send New Message (via Mobile App)
</a> -->
            
            <!-- Fixed: Removed route('messages.create') since it doesn't exist -->
            <a href="{{ route('dashboard.messages') }}" 
               onclick="alert('Public messages are currently sent via the mobile app.\n\nUse the API endpoint: POST /api/send-message')"
               class="bg-emerald-600 hover:bg-emerald-700 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2 cursor-pointer">
                <i class="fas fa-paper-plane"></i> Send New Message
            </a>
        </div>

        <!-- Rest of your table remains the same -->
        <table class="w-full">
                <thead class="bg-zinc-950 sticky top-0">
                    <tr class="border-b border-zinc-800">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Username</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Message</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Location</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">County / Constituency</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Time</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-zinc-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse($messages as $msg)
                        <tr class="hover:bg-zinc-800/70 transition-colors">
                            <td class="px-6 py-4 font-medium text-white">{{ $msg->username }}</td>
                            <td class="px-6 py-4 text-zinc-300 line-clamp-2 max-w-md pr-8">
                                {{ $msg->message }}
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-500 font-mono">
                                @if($msg->latitude && $msg->longitude)
                                    {{ number_format($msg->latitude, 5) }}, {{ number_format($msg->longitude, 5) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="text-white">{{ $msg->constituency ?? '—' }}</span>
                                <span class="text-zinc-500 text-xs block">{{ $msg->county ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-500 whitespace-nowrap">
                                {{ $msg->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="deleteMessage({{ $msg->id }}, '{{ addslashes($msg->username) }}')"
                                        class="text-red-400 hover:text-red-500 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center text-zinc-500">
                                No public messages have been sent yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>

    <!-- Pagination -->
    @if($messages->hasPages())
    <div class="mt-6">
        {{ $messages->links() }}
    </div>
    @endif
</div>

    <!-- ==================== COUNTY / CONSTITUENCY SECTION ==================== -->
    <!-- <div id="content-counties" class="tab-content">
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-zinc-800 bg-zinc-950 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                    <i class="fas fa-globe"></i> 
                    Public Constituency Messages
                </h2>
                <a href="{{ route('messages.create') ?? '#' }}" 
                   class="bg-emerald-600 hover:bg-emerald-700 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Send New Message
                </a>
            </div>

            <table class="w-full">
                <thead class="bg-zinc-950 sticky top-0">
                    <tr class="border-b border-zinc-800">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Username</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Message</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Location</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">County / Constituency</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse($messages ?? [] as $msg)
                        <tr class="hover:bg-zinc-800/70 transition-colors">
                            <td class="px-6 py-4 font-medium text-white">{{ $msg->username }}</td>
                            <td class="px-6 py-4 text-zinc-300 line-clamp-2 max-w-md pr-8">
                                {{ $msg->message }}
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-500 font-mono">
                                @if($msg->latitude && $msg->longitude)
                                    {{ number_format($msg->latitude, 5) }}, {{ number_format($msg->longitude, 5) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="text-white">{{ $msg->constituency ?? '—' }}</span>
                                <span class="text-zinc-500 text-xs block">{{ $msg->county ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-500 whitespace-nowrap">
                                {{ $msg->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center text-zinc-500">
                                No public messages have been sent yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div> -->

    <!-- ==================== GROUPS SECTION ==================== -->
    <div id="content-groups" class="tab-content hidden">
        @if(isset($groups) && $groups->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($groups as $group)
                    <div class="bg-zinc-900 border border-zinc-800 hover:border-violet-500 rounded-3xl overflow-hidden transition-all duration-300 group">
                        <div class="p-7">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-semibold text-white line-clamp-1">{{ $group->name }}</h3>
                                <span class="bg-zinc-800 text-emerald-400 text-xs px-3 py-1 rounded-full font-mono">
                                    {{ $group->members_count ?? 0 }}
                                </span>
                            </div>
                            
                            @if($group->description)
                                <p class="text-zinc-400 text-sm line-clamp-3 mb-6">{{ $group->description }}</p>
                            @endif

                            <div class="flex items-center justify-between text-xs text-zinc-500 mb-6">
                                <div>
                                    Invite Code: 
                                    <span class="font-mono bg-zinc-950 px-2 py-0.5 rounded">{{ $group->invite_code }}</span>
                                </div>
                                <div class="text-emerald-500">
                                    {{ $group->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <a href="{{ route('groups.show', $group) }}" 
                               class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium transition-colors">
                                Open Group Chat →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl py-20 px-10 text-center">
                <div class="mx-auto w-20 h-20 bg-zinc-800 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-users text-4xl text-zinc-600"></i>
                </div>
                <h3 class="text-2xl font-medium text-zinc-300 mb-3">No Groups Yet</h3>
                <p class="text-zinc-500 max-w-md mx-auto mb-8">
                    Create your own discussion group or join existing ones using invite codes.
                </p>
                <a href="{{ route('groups.create') }}" 
                   class="inline-flex items-center gap-3 bg-violet-600 hover:bg-violet-700 px-8 py-4 rounded-2xl font-semibold">
                    <i class="fas fa-plus"></i> 
                    Create Your First Group
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="bg-zinc-900 rounded-3xl p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold mb-4">Delete Message?</h3>
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

<script>
let currentDeleteId = null;

function deleteMessage(id, username) {
    currentDeleteId = id;
    document.getElementById('deleteMessage').innerHTML =
        `Are you sure you want to permanently delete the message from <strong>${username}</strong>?<br><br>This action cannot be undone.`;

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

    fetch(`/messages/${currentDeleteId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Remove the message from the DOM instead of reloading
            const messageRow = document.querySelector(`button[onclick*="deleteMessage(${currentDeleteId}"]`)?.closest('tr');
            if (messageRow) {
                messageRow.remove();
            }
            // Show success message
            alert('Message deleted successfully!');
        } else {
            alert(data.message || 'Failed to delete message');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('An error occurred while deleting the message: ' + error.message);
    });
}

function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active', 'border-emerald-500', 'text-emerald-400');
        btn.classList.add('text-zinc-400');
    });

    if (tab === 0) {
        document.getElementById('content-counties').classList.remove('hidden');
        document.getElementById('tab-counties').classList.add('active', 'border-emerald-500', 'text-emerald-400');
    } else {
        document.getElementById('content-groups').classList.remove('hidden');
        document.getElementById('tab-groups').classList.add('active', 'border-emerald-500', 'text-emerald-400');
    }
}

// Load Counties tab by default
document.addEventListener('DOMContentLoaded', () => {
    switchTab(0);
});
</script>
@endsection