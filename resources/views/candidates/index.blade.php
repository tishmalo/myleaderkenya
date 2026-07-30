@extends('layouts.app')

@section('page_title', 'Candidates Management')

@section('content')
<div class="mx-auto w-full max-w-7xl">
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


    @foreach(['success' => 'emerald', 'warning' => 'amber', 'error' => 'red'] as $flashKey => $flashColor)
        @if(session($flashKey))
            <div class="mb-6 rounded-2xl border border-{{ $flashColor }}-700/60 bg-{{ $flashColor }}-950/50 px-5 py-4 text-{{ $flashColor }}-100">
                {{ session($flashKey) }}
            </div>
        @endif
    @endforeach
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
                    <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('approval_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
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
        <div class="w-full max-w-full overflow-x-auto">
        <table class="min-w-[1200px] w-full">
            <thead class="bg-zinc-950 sticky top-0">
                <tr class="border-b border-zinc-800">
                    <th class="px-6 py-4 text-left">Candidate</th>
                    <th class="px-6 py-4 text-left">Position</th>
                    <th class="px-6 py-4 text-left">Political Party</th>
                    <th class="px-6 py-4 text-left">Jurisdiction</th>
                    <th class="px-6 py-4 text-center">Approval</th>
                    <th class="px-6 py-4 text-center">Featured</th>
                    <th class="px-6 py-4 text-center">Account Claim</th>
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
                        <select data-approval-select data-url="{{ route('candidates.approval', $candidate) }}" class="rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm text-white">
                            <option value="pending" {{ ($candidate->approval_status ?? 'approved') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ ($candidate->approval_status ?? 'approved') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ ($candidate->approval_status ?? 'approved') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
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
                        @php
                            $pendingClaims = (int) ($candidate->pending_claim_requests_count ?? 0);
                            $approvedClaims = (int) ($candidate->approved_claim_requests_count ?? 0);
                            $rejectedClaims = (int) ($candidate->rejected_claim_requests_count ?? 0);
                            $totalClaims = $pendingClaims + $approvedClaims + $rejectedClaims;
                            $rowLoginUser = $candidate->user
                                ?: $candidate->claimRequests->first(fn ($claimRequest) => $claimRequest->status === 'approved' && $claimRequest->user)?->user;
                        @endphp
                        <button type="button"
                                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $pendingClaims > 0 ? 'bg-amber-900/50 text-amber-300' : ($approvedClaims > 0 ? 'bg-emerald-900/50 text-emerald-300' : 'bg-zinc-800 text-zinc-300') }}"
                                data-claim-review-open="{{ $candidate->id }}">
                            <i class="fas {{ $pendingClaims > 0 ? 'fa-clock' : ($approvedClaims > 0 ? 'fa-check-circle' : 'fa-user-check') }}"></i>
                            {{ $pendingClaims > 0 ? $pendingClaims . ' Pending' : ($approvedClaims > 0 ? $approvedClaims . ' Approved' : 'No Requests') }}
                        </button>
                        @if($rejectedClaims > 0)
                            <p class="mt-2 text-xs text-zinc-500">{{ $rejectedClaims }} rejected</p>
                        @endif
                        @if($rowLoginUser)
                            <form method="POST" action="{{ route('candidates.login-as', [$candidate, $rowLoginUser]) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-blue-700 px-3 py-1 text-xs font-semibold text-blue-300 hover:bg-blue-950" title="Login as {{ $rowLoginUser->name }}">
                                    <i class="fas fa-right-to-bracket"></i> Login as
                                </button>
                            </form>
                        @else
                            <button type="button"
                                    class="mt-2 inline-flex cursor-not-allowed items-center gap-2 rounded-full border border-zinc-700 px-3 py-1 text-xs font-semibold text-zinc-500"
                                    title="Approve a claim request or send a claim link before logging in as this aspirant.">
                                <i class="fas fa-right-to-bracket"></i> Login as
                            </button>
                        @endif
                        @if(blank($candidate->email))
                            <p class="mt-2 text-xs text-amber-300">Needs email for token link</p>
                        @elseif(!($candidate->user_id || $candidate->claimed_at))
                            <form method="POST" action="{{ route('candidates.claim-link', $candidate) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-emerald-700 px-3 py-1 text-xs font-semibold text-emerald-300 hover:bg-emerald-950">
                                    <i class="fas fa-envelope"></i>
                                    {{ $candidate->claim_sent_at ? 'Resend Link' : 'Send Link' }}
                                </button>
                            </form>
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
                    <td colspan="8" class="px-6 py-16 text-center text-zinc-500">
                        No candidates found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @foreach($candidates as $candidate)
        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 p-5 backdrop-blur-sm" data-claim-review-modal="{{ $candidate->id }}">
            <div class="max-h-[90vh] w-full max-w-5xl overflow-auto rounded-3xl border border-zinc-700 bg-zinc-950 shadow-2xl">
                <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-zinc-800 bg-zinc-950 px-6 py-5">
                    <div>
                        <h2 class="text-2xl font-semibold text-white">Account Claims</h2>
                        <p class="mt-1 text-sm text-zinc-400">{{ $candidate->name }} &bull; {{ $candidate->position->name ?? 'Aspirant' }}</p>
                    </div>
                    <button type="button" class="grid h-11 w-11 place-items-center rounded-xl border border-zinc-700 text-white hover:bg-zinc-800" data-claim-review-close aria-label="Close account claims">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    @if($candidate->claimRequests->isEmpty())
                        <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-8 text-center text-zinc-400">
                            No claim requests have been submitted for this aspirant.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-zinc-800">
                            <table class="w-full min-w-[920px]">
                                <thead class="bg-zinc-900 text-left text-sm text-zinc-400">
                                    <tr>
                                        <th class="px-4 py-3">Claimant</th>
                                        <th class="px-4 py-3">Role</th>
                                        <th class="px-4 py-3">Phone</th>
                                        <th class="px-4 py-3">Submitted</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3 text-right">Review</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800">
                                    @foreach($candidate->claimRequests as $claimRequest)
                                        <tr>
                                            <td class="px-4 py-4">
                                                <div class="font-semibold text-white">{{ $claimRequest->name }}</div>
                                                <div class="text-sm text-zinc-500">{{ $claimRequest->email }}</div>
                                            </td>
                                            <td class="px-4 py-4 text-zinc-300">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $claimRequest->relationship)) }}</td>
                                            <td class="px-4 py-4 text-zinc-400">{{ $claimRequest->phone ?? '-' }}</td>
                                            <td class="px-4 py-4 text-zinc-400">{{ $claimRequest->created_at->format('d M Y H:i') }}</td>
                                            <td class="px-4 py-4">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $claimRequest->status === 'approved' ? 'bg-emerald-900/50 text-emerald-300' : ($claimRequest->status === 'rejected' ? 'bg-red-900/50 text-red-300' : 'bg-amber-900/50 text-amber-300') }}">
                                                    {{ ucfirst($claimRequest->status) }}
                                                </span>
                                                @if($claimRequest->reviewer)
                                                    <div class="mt-1 text-xs text-zinc-500">By {{ $claimRequest->reviewer->name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4">
                                                @if($claimRequest->status === 'pending')
                                                    <div class="flex justify-end gap-2">
                                                        <form method="POST" action="{{ route('candidate-claim-requests.update', $claimRequest) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="approved">
                                                            <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Approve</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('candidate-claim-requests.update', $claimRequest) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button type="submit" class="rounded-xl border border-red-700 px-4 py-2 text-sm font-semibold text-red-300 hover:bg-red-950">Reject</button>
                                                        </form>
                                                    </div>
                                                @elseif($claimRequest->status === 'approved' && $claimRequest->user)
                                                    @php
                                                        $linkedCandidate = $claimRequest->user->relatedCandidates->firstWhere('id', $candidate->id);
                                                        $dashboardAccessEnabled = (bool) ($linkedCandidate?->pivot?->dashboard_access_enabled ?? true);
                                                    @endphp
                                                    <div class="flex justify-end gap-2">
                                                        <form method="POST" action="{{ route('candidates.login-as', [$candidate, $claimRequest->user]) }}">
                                                            @csrf
                                                            <button type="submit" class="rounded-xl border border-blue-700 px-4 py-2 text-sm font-semibold text-blue-300 hover:bg-blue-950">Login as</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('candidate-claim-requests.dashboard-access', $claimRequest) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="dashboard_access_enabled" value="{{ $dashboardAccessEnabled ? 0 : 1 }}">
                                                            <button type="submit" class="rounded-xl border {{ $dashboardAccessEnabled ? 'border-amber-700 text-amber-300 hover:bg-amber-950' : 'border-emerald-700 text-emerald-300 hover:bg-emerald-950' }} px-4 py-2 text-sm font-semibold">
                                                                {{ $dashboardAccessEnabled ? 'Disable Dashboard' : 'Enable Dashboard' }}
                                                            </button>
                                                        </form>
                                                        <div class="self-center text-right text-xs text-zinc-500">
                                                            {{ $dashboardAccessEnabled ? 'Access on' : 'Access off' }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-right text-sm text-zinc-500">{{ $claimRequest->reviewed_at?->format('d M Y H:i') ?? '-' }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <!-- Pagination -->
    <div class="mt-8 flex justify-center">
        {{ $candidates->appends(request()->query())->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-claim-review-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = document.querySelector(`[data-claim-review-modal="${button.dataset.claimReviewOpen}"]`);
            modal?.classList.remove('hidden');
            modal?.classList.add('flex');
        });
    });

    document.querySelectorAll('[data-claim-review-modal]').forEach((modal) => {
        const close = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        modal.querySelectorAll('[data-claim-review-close]').forEach((button) => button.addEventListener('click', close));
        modal.addEventListener('click', (event) => {
            if (event.target === modal) close();
        });
    });
});

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

document.querySelectorAll('[data-approval-select]').forEach(function (select) {
    select.addEventListener('change', function () {
        var previous = select.dataset.previous || select.defaultValue || 'approved';
        select.disabled = true;

        fetch(select.dataset.url, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ approval_status: select.value })
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Request failed');
                return response.json();
            })
            .then(function (data) {
                select.value = data.approval_status;
                select.dataset.previous = data.approval_status;
            })
            .catch(function () {
                select.value = previous;
                alert('Could not update approval status. Please try again.');
            })
            .finally(function () {
                select.disabled = false;
            });
    });
    select.dataset.previous = select.value;
});
</script>
@endpush





