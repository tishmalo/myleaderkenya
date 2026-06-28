@extends('layouts.app')

@section('page_title', 'Candidates Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-users text-emerald-500"></i> 
            Candidates Management
        </h1>
        
        <a href="{{ route('candidates.create') }}" 
           class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i> Add New Candidate
        </a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950 sticky top-0">
                <tr class="border-b border-zinc-800">
                    <th class="px-6 py-4 text-left">Candidate</th>
                    <th class="px-6 py-4 text-left">Position</th>
                    <th class="px-6 py-4 text-left">Political Party</th>
                    <th class="px-6 py-4 text-left">Jurisdiction</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($candidates as $candidate)
                <tr class="hover:bg-zinc-800/70 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($candidate->profile_picture)
                                <img src="{{ Storage::url($candidate->profile_picture) }}" 
                                     class="w-10 h-10 rounded-full object-cover border border-zinc-700">
                            @else
                                <div class="w-10 h-10 bg-zinc-700 rounded-full flex items-center justify-center text-zinc-400">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-white">{{ $candidate->name }}</p>
                                <p class="text-sm text-zinc-500">{{ $candidate->nick_name ?? '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-medium">{{ $candidate->position->name ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-zinc-400">
                        {{ $candidate->politicalParty->name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-400">
                        @if($candidate->county)
                            <strong>{{ $candidate->county }}</strong><br>
                            @if($candidate->constituency)
                                {{ $candidate->constituency }}
                                @if($candidate->ward) • {{ $candidate->ward }} @endif
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex gap-4 justify-center">
                            <a href="{{ route('candidates.edit', $candidate) }}" 
                               class="text-blue-400 hover:text-blue-500 transition-colors">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteCandidate({{ $candidate->id }}, '{{ addslashes($candidate->name) }}')" 
                                    class="text-red-400 hover:text-red-500 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-zinc-500">
                        No candidates found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-8 flex justify-center">
        {{ $candidates->appends(request()->query())->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteCandidate(id, name) {
    const message = `Are you sure you want to delete <strong>${name}</strong>?<br><br>This action cannot be undone.`;
    
    showDeleteModal(`/candidates/${id}`, message);
}
</script>
@endpush