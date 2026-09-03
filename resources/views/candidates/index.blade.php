@extends('layouts.app')

@section('page_title', 'Candidates Management')

@section('content')
<div class="mx-auto w-full max-w-7xl">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-users text-emerald-500"></i>
            Candidates Management
        </h1>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button"
                    onclick="document.getElementById('importModal').classList.remove('hidden'); document.getElementById('importModal').classList.add('flex');"
                    class="bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2 text-white">
                <i class="fas fa-file-csv"></i> Import CSV
            </button>

            <form method="POST" action="{{ route('candidates.export') }}" class="inline">
                @csrf
                @foreach(request()->only(['candidate', 'position', 'political_party', 'approval_status', 'account_claim', 'import_filter']) as $key => $value)
                    @if($value !== '' && $value !== null)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <button type="submit"
                        class="bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2 text-white">
                    <i class="fas fa-file-export"></i> Export CSV
                </button>
            </form>

            <a href="{{ route('candidates.create') }}"
               class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
                <i class="fas fa-plus"></i> Add New Candidate
            </a>
        </div>
    </div>


    @foreach(['success' => 'emerald', 'warning' => 'amber', 'error' => 'red'] as $flashKey => $flashColor)
        @if(session($flashKey))
            <div class="mb-6 rounded-2xl border border-{{ $flashColor }}-700/60 bg-{{ $flashColor }}-950/50 px-5 py-4 text-{{ $flashColor }}-100">
                {{ session($flashKey) }}
            </div>
        @endif
    @endforeach
    <form method="GET" action="{{ route('candidates.index') }}" class="mb-6 bg-zinc-900 border border-zinc-800 rounded-3xl p-5">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
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
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Account Claim</label>
                <select name="account_claim" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                    <option value="">All Accounts</option>
                    @foreach([
                        'claimed_pending' => 'Claimed - Pending Approval',
                        'claimed_approved' => 'Claimed - Approved',
                        'claim_sent' => 'Claim Link Sent (Unclaimed)',
                        'unclaimed' => 'Unclaimed',
                    ] as $value => $label)
                        <option value="{{ $value }}" {{ request('account_claim') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Import</label>
                <select name="import_filter" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                    <option value="">All</option>
                    <option value="imported" {{ request('import_filter') === 'imported' ? 'selected' : '' }}>Imported</option>
                    <option value="imported_pending" {{ request('import_filter') === 'imported_pending' ? 'selected' : '' }}>Imported – Not published</option>
                    <option value="imported_published" {{ request('import_filter') === 'imported_published' ? 'selected' : '' }}>Imported – Published</option>
                    <option value="not_imported" {{ request('import_filter') === 'not_imported' ? 'selected' : '' }}>Not imported</option>
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
    @if($transferRuns->isNotEmpty())
    <div class="mb-6 bg-zinc-900 border border-zinc-800 rounded-3xl p-5" data-transfer-panel>
        <div class="mb-4 flex items-center gap-2">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-emerald-500"></i> Import / Export Jobs
            </h2>
            <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs text-zinc-400">updates automatically</span>
        </div>
        <div class="space-y-3">
            @foreach($transferRuns as $run)
                <div class="flex flex-wrap items-center gap-x-3 gap-y-2 rounded-2xl border border-zinc-800 bg-zinc-950 px-4 py-3"
                     data-transfer-run="{{ $run->id }}"
                     data-transfer-url="{{ route('candidates.transfer.status', $run) }}">
                    <span class="inline-flex rounded-full bg-zinc-800 px-3 py-1 text-xs font-semibold uppercase text-zinc-300">
                        {{ $run->type }}
                    </span>
                    <span class="text-xs font-semibold uppercase {{ $run->status === 'complete' ? 'text-emerald-400' : ($run->status === 'failed' ? 'text-red-400' : 'text-amber-300') }}"
                          data-transfer-status>{{ $run->status }}</span>
                    <span class="text-xs text-zinc-500">{{ $run->created_at->diffForHumans() }}</span>
                    <span class="ml-auto text-xs text-zinc-400" data-transfer-summary>
                        @if($run->type === 'import' && $run->status === 'complete')
                            {{ $run->imported_count }} imported, {{ $run->linked_count }} linked, {{ $run->skipped_count }} skipped
                        @elseif($run->type === 'export' && $run->status === 'complete')
                            {{ $run->exported_count }} exported
                        @elseif(in_array($run->status, ['pending', 'running'], true))
                            Processing&hellip;
                        @endif
                    </span>
                    @if($run->status === 'complete' && $run->type === 'export' && $run->result_path)
                        <a href="{{ route('candidates.export.download', $run) }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            <i class="fas fa-download"></i> Download
                        </a>
                    @endif
                    @if($run->status === 'failed' && $run->error_message)
                        <span class="w-full text-xs text-red-300" title="{{ $run->error_message }}">
                            <i class="fas fa-circle-exclamation mr-1"></i>{{ \Illuminate\Support\Str::limit($run->error_message, 160) }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="w-full max-w-full overflow-x-auto">
        <table class="min-w-[1320px] w-full">
            <thead class="bg-zinc-950 sticky top-0">
                <tr class="border-b border-zinc-800">
                    <th class="px-6 py-4 text-left">Candidate</th>
                    <th class="px-6 py-4 text-left">Position</th>
                    <th class="px-6 py-4 text-left">Political Party</th>
                    <th class="px-6 py-4 text-left">Jurisdiction</th>
                    <th class="px-6 py-4 text-left">Added By</th>
                    <th class="px-6 py-4 text-center">Status</th>
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
                                @if(($candidate->paid_support_count ?? 0) > 0)
                                    <p class="mt-1 text-xs font-semibold text-emerald-400">Donations: KES {{ number_format((float) $candidate->paid_support_gross_sum, 2) }}</p>
                                @endif
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
                    <td class="px-6 py-4 text-sm">
                        @if($candidate->creatorAudit)
                            <span class="font-medium text-white">{{ $candidate->creatorAudit->user?->name ?? 'System' }}</span>
                            <div class="mt-1 text-xs text-zinc-500">{{ $candidate->creatorAudit->created_at?->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</div>
                        @else
                            <span class="text-zinc-500">Not recorded</span>
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
                                                        @if(($candidate->approval_status ?? 'approved') !== 'approved')
                                <form method="POST" action="{{ route('candidates.approval', $candidate) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button class="text-emerald-400 hover:text-emerald-500" title="Approve"><i class="fas fa-check"></i></button>
                                </form>
                            @endif
                            @if(($candidate->approval_status ?? 'approved') !== 'rejected')
                                <form method="POST" action="{{ route('candidates.approval', $candidate) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="text-amber-400 hover:text-amber-500" title="Reject"><i class="fas fa-ban"></i></button>
                                </form>
                            @endif
                            @if(($candidate->is_imported ?? false) && ($candidate->import_status ?? null) === 'pending')
                                <form method="POST" action="{{ route('candidates.import.publish', $candidate) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-emerald-400 hover:text-emerald-500" title="Publish imported">
                                        <i class="fas fa-upload"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('candidates.import.discard', $candidate) }}" class="inline"
                                      onsubmit="return confirm('Discard this imported aspirant?');">
                                    @csrf
                                    <button type="submit" class="text-zinc-400 hover:text-zinc-200" title="Discard imported">
                                        <i class="fas fa-ban"></i>
                                    </button>
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
                    <td colspan="9" class="px-6 py-16 text-center text-zinc-500">
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
                        @if($candidate->is_imported)
                            <p class="mt-1 text-xs font-semibold text-sky-400">
                                Imported
                                @if($candidate->import_status === 'pending') – Not published
                                @elseif($candidate->import_status === 'published') – Published
                                @endif
                            </p>
                            @if($candidate->linked_candidate_id && $candidate->linkedCandidate)
                                <p class="mt-1 text-xs text-amber-300">
                                    Linked to:
                                    <a href="{{ route('candidates.edit', $candidate->linkedCandidate) }}" class="underline">
                                        {{ $candidate->linkedCandidate->name }} #{{ $candidate->linkedCandidate->id }}
                                    </a>
                                </p>
                            @endif
                        @endif
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
                                                @elseif($claimRequest->status === 'approved' && $claimRequest->user && $claimRequest->relationship !== 'adopter')
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
                                                @elseif($claimRequest->status === 'approved' && $claimRequest->relationship === 'adopter')
                                                    <div class="text-right text-sm text-emerald-300">Sponsorship only · No dashboard access</div>
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

{{-- Import Modal --}}
<div id="importModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 p-5 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-3xl border border-zinc-700 bg-zinc-950 shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-zinc-800 px-6 py-5">
            <div>
                <h2 class="text-xl font-semibold text-white">Import Candidates</h2>
                <p class="mt-1 text-sm text-zinc-400">
                    Upload a <strong>CSV (UTF-8)</strong> file. In Excel: File &rarr; Save As &rarr; CSV UTF-8.
                </p>
            </div>
            <button type="button"
                    onclick="document.getElementById('importModal').classList.add('hidden'); document.getElementById('importModal').classList.remove('flex');"
                    class="grid h-10 w-10 place-items-center rounded-xl border border-zinc-700 text-white hover:bg-zinc-800">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('candidates.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf

            @error('file')
                <div class="rounded-2xl border border-red-700/60 bg-red-950/50 px-4 py-3 text-sm text-red-100">
                    {{ $message }}
                </div>
            @enderror

            <div>
                <label class="block text-sm text-zinc-400 mb-2">CSV file</label>
                <input type="file" name="file" accept=".csv,text/csv,text/plain" required
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                <p class="mt-2 text-xs text-zinc-500">Accepted: .csv only (not .xlsx).</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('candidates.import.template') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-zinc-700 px-4 py-2.5 text-sm text-zinc-300 hover:bg-zinc-800">
                    <i class="fas fa-download"></i> Download template
                </a>
            </div>

            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 py-3.5 rounded-2xl font-semibold text-white">
                Import Candidates
            </button>
        </form>
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

document.addEventListener('DOMContentLoaded', () => {
    const rows = document.querySelectorAll('[data-transfer-run]');
    if (!rows.length) return;

    const statusClass = (status) => 'text-xs font-semibold uppercase ' + (status === 'complete' ? 'text-emerald-400' : (status === 'failed' ? 'text-red-400' : 'text-amber-300'));

    const updateRow = (runId, data) => {
        const row = document.querySelector(`[data-transfer-run="${runId}"]`);
        if (!row) return;

        const statusEl = row.querySelector('[data-transfer-status]');
        const summaryEl = row.querySelector('[data-transfer-summary]');

        if (statusEl) {
            statusEl.textContent = data.status;
            statusEl.className = statusClass(data.status);
        }

        if (summaryEl) {
            if (data.type === 'import' && data.status === 'complete') {
                summaryEl.textContent = `${data.imported_count} imported, ${data.linked_count} linked, ${data.skipped_count} skipped`;
            } else if (data.type === 'export' && data.status === 'complete') {
                summaryEl.textContent = `${data.exported_count} exported`;
            } else if (data.status === 'pending' || data.status === 'running') {
                summaryEl.textContent = 'Processing…';
            }
        }

        if (data.status === 'failed' && data.error_message) {
            let errEl = row.querySelector('[data-transfer-error]');
            if (!errEl) {
                errEl = document.createElement('span');
                errEl.className = 'w-full text-xs text-red-300';
                errEl.dataset.transferError = '';
                row.appendChild(errEl);
            }
            errEl.textContent = '⚠ ' + data.error_message;
        }

        if (data.status === 'complete' && data.type === 'export' && data.download_url) {
            let link = row.querySelector('[data-transfer-download]');
            if (!link) {
                link = document.createElement('a');
                link.className = 'inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700';
                link.dataset.transferDownload = '';
                link.innerHTML = '<i class="fas fa-download"></i> Download';
                row.appendChild(link);
            }
            link.href = data.download_url;
        }
    };

    const poll = () => {
        let active = false;

        rows.forEach((row) => {
            const status = row.querySelector('[data-transfer-status]')?.textContent.trim();
            if (status !== 'pending' && status !== 'running') return;
            active = true;

            fetch(row.dataset.transferUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then((response) => response.json())
                .then((data) => updateRow(row.dataset.transferRun, data))
                .catch(() => {});
        });

        if (active) setTimeout(poll, 4000);
    };

    poll();
});
</script>
@endpush





