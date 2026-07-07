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

    <form method="GET" action="{{ route('candidates.index') }}" class="mb-6 bg-zinc-900 border border-zinc-800 rounded-3xl p-5">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Candidate</label>
                <input type="text" name="candidate" value="{{ request('candidate') }}"
                       placeholder="Search name or nickname"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white placeholder:text-zinc-500 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Position</label>
                <select name="position" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                    <option value="">All Positions</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}" {{ request('position') == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Political Party</label>
                <select name="political_party" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                    <option value="">All Political Parties</option>
                    @foreach($politicalParties as $party)
                        <option value="{{ $party->id }}" {{ request('political_party') == $party->id ? 'selected' : '' }}>
                            {{ $party->abbreviation ? $party->abbreviation . ' - ' : '' }}{{ $party->name }}
                        </option>
                    @endforeach
                </select>
            </div>
                        <div>
                <label class="block text-sm text-zinc-400 mb-2">Approval</label>
                <select name="approval_status" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                    <option value="">All Statuses</option>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" {{ request('approval_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl font-semibold text-white">
                    Filter
                </button>
                <a href="{{ route('candidates.index') }}" class="px-5 py-3 rounded-2xl border border-zinc-700 text-zinc-300 hover:bg-zinc-800">
                    Clear
                </a>
            </div>
        </div>
    </form>
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950 sticky top-0">
                <tr class="border-b border-zinc-800">
                    <th class="px-6 py-4 text-left">Candidate</th>
                    <th class="px-6 py-4 text-left">Position</th>
                    <th class="px-6 py-4 text-left">Political Party</th>
                    <th class="px-6 py-4 text-left">Jurisdiction</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Featured</th>
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
                                     class="w-10 h-10 rounded-full object-cover border border-zinc-700"
                                     alt="{{ $candidate->name }}">
                            @else
                                <div class="w-10 h-10 bg-zinc-700 rounded-full flex items-center justify-center text-zinc-400">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-white">{{ $candidate->name }}</p>
                                <p class="text-sm text-zinc-500">{{ $candidate->nick_name ?? '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-medium">{{ $candidate->position->name ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-4 text-zinc-400">
                        {{ $candidate->politicalParty->name ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-400">
                        @if($candidate->county)
                            <strong>{{ $candidate->county }}</strong><br>
                            @if($candidate->constituency)
                                {{ $candidate->constituency }}
                                @if($candidate->ward) &bull; {{ $candidate->ward }} @endif
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-xl text-xs font-semibold {{ $candidate->approval_status === 'approved' ? 'bg-emerald-500/10 text-emerald-400' : ($candidate->approval_status === 'rejected' ? 'bg-red-500/10 text-red-400' : 'bg-amber-500/10 text-amber-300') }}">
                            {{ ucfirst($candidate->approval_status ?? 'approved') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <label class="inline-flex cursor-pointer items-center justify-center" title="Show in homepage aspirants carousel">
                            <input type="checkbox"
                                   class="sr-only peer"
                                   data-featured-toggle
                                   data-url="{{ route('candidates.featured', $candidate) }}"
                                   {{ $candidate->featured ? 'checked' : '' }}>
                            <span class="relative h-6 w-11 rounded-full bg-zinc-700 transition-colors peer-checked:bg-emerald-600 after:absolute after:left-1 after:top-1 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-transform peer-checked:after:translate-x-5"></span>
                        </label>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex gap-4 justify-center">
                                                        @if(($candidate->approval_status ?? 'approved') !== 'approved')
                                <form method="POST" action="{{ route('candidates.approve', $candidate) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button class="text-emerald-400 hover:text-emerald-500" title="Approve"><i class="fas fa-check"></i></button>
                                </form>
                            @endif
                            @if(($candidate->approval_status ?? 'approved') !== 'rejected')
                                <form method="POST" action="{{ route('candidates.reject', $candidate) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button class="text-amber-400 hover:text-amber-500" title="Reject"><i class="fas fa-ban"></i></button>
                                </form>
                            @endif
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
                    <td colspan="7" class="px-6 py-16 text-center text-zinc-500">
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

document.querySelectorAll('[data-featured-toggle]').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
        var checked = toggle.checked;
        toggle.disabled = true;

        fetch(toggle.dataset.url, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ featured: checked })
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Request failed');
                return response.json();
            })
            .then(function (data) {
                toggle.checked = Boolean(data.featured);
            })
            .catch(function () {
                toggle.checked = !checked;
                alert('Could not update featured status. Please try again.');
            })
            .finally(function () {
                toggle.disabled = false;
            });
    });
});
</script>
@endpush




