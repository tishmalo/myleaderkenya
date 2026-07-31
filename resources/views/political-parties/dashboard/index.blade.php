@extends('layouts.landing')
@section('title',$party->name.' Party Dashboard')
@section('content')
@include('components.frontend-nav')
<style>
    .pd {
        max-width: 1400px;
        margin: auto;
        padding: 32px;
        color: #f5f5f0;
    }

    .pd-card {
        padding: 22px;
        border: 1px solid #2b2b2b;
        border-radius: 20px;
        background: #151515;
    }

    .pd-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .pd-tabs {
        display: grid;
        gap: 24px;
        margin-top: 25px;
    }

    .pd table {
        width: 100%;
        border-collapse: collapse;
    }

    .pd th,
    .pd td {
        padding: 12px;
        border-bottom: 1px solid #292929;
        text-align: left;
    }

    .pd input,
    .pd select {
        padding: 10px;
        border: 1px solid #3a3a3a;
        border-radius: 10px;
        background: #242424;
        color: white;
    }

    .pd button,
    .pd .btn {
        display: inline-block;
        padding: 10px 15px;
        border: 0;
        border-radius: 10px;
        background: #009b68;
        color: white;
        text-decoration: none;
    }

    .pd .danger {
        background: #7f1d1d;
    }

    @media (max-width: 900px) {
        .pd-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 560px) {
        .pd {
            padding: 18px;
        }

        .pd-grid {
            grid-template-columns: 1fr;
        }

        .pd-card {
            overflow: auto;
        }
    }
</style>
<main class="pd">
@if(session('success'))<div class="pd-card" style="border-color:#059669;margin-bottom:18px">{{ session('success') }}</div>
@endif
 @if(session('warning'))<div class="pd-card" style="border-color:#b45309;margin-bottom:18px">{{ session('warning') }}</div>
@endif

<div style="display:flex;justify-content:space-between;gap:20px;align-items:center;flex-wrap:wrap">
<div>
<p style="color:#10b981;text-transform:uppercase">Political Party Workspace</p>
<h1 style="font-size:38px;margin:0">{{ $party->name }}</h1>
<p style="color:#999">{{ str($membership->role)->headline() }}</p>
</div>
<a class="btn" href="{{ route('party.candidates.create') }}">+ Add Aspirant</a>
</div>
<div class="pd-grid" style="margin-top:24px">
<div class="pd-card">
<small>Party tokens</small>
<h2>{{ number_format($wallet->balance) }}</h2>
</div>
<div class="pd-card">
<small>Aspirants</small>
<h2>{{ number_format($candidates->total()) }}</h2>
</div>
<div class="pd-card">
<small>Officials</small>
<h2>{{ $officials->count() }}</h2>
</div>
<div class="pd-card">
<small>Pending claims</small>
<h2>{{ $claims->where('status','pending')->count() }}</h2>
</div>
</div>
<div class="pd-tabs">
<section class="pd-card">
<h2>Aspirants</h2>
<form method="GET" class="pd-grid" style="margin-bottom:18px">
<input name="search" value="{{ request('search') }}" placeholder="Search">
<select name="position">
<option value="">All positions</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected(request('position')==$position->id)>{{ $position->name }}</option>
@endforeach
</select>
<select name="approval_status">
<option value="">All statuses</option>@foreach(['pending','approved','rejected'] as $s)<option @selected(request('approval_status')===$s)>{{ $s }}</option>
@endforeach
</select>
<button>Filter</button>
</form>
<table>
<thead>
<tr>
<th>Aspirant</th>
<th>Position</th>
<th>Status</th>
<th>Tokens</th>
<th>
</th>
</tr>
</thead>
<tbody>@forelse($candidates as $candidate)<tr>
<td>{{ $candidate->name }}</td>
<td>{{ $candidate->position->name??'-' }}</td>
<td>{{ ucfirst($candidate->approval_status) }}</td>
<td>{{ number_format($candidate->tokenWallet->balance??0) }}</td>
<td>
<a class="btn" href="{{ route('party.candidates.edit',$candidate) }}">Edit</a>
</td>
</tr>
@empty
<tr>
<td colspan="5">No aspirants found.</td>
</tr>
@endforelse
</tbody>
</table>
<div style="margin-top:18px">{{ $candidates->links() }}</div>
</section>
<section class="pd-grid">
<div class="pd-card" style="grid-column:span 2">
<h2>Distribute tokens</h2>
<form method="POST" action="{{ route('party.tokens.distribute') }}" style="display:flex;gap:10px;flex-wrap:wrap">

@csrf

<select name="candidate_id" required>
<option value="">Approved aspirant</option>@foreach($eligibleCandidates as $candidate)<option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
@endforeach
</select>
<input type="number" min="1" name="amount" placeholder="Tokens" required>
<button>Transfer tokens</button>
</form>
<h3 style="margin-top:24px">Recent distributions</h3>
@foreach($transactions as $tx)
<p>
    {{ $tx->created_at->format('d M Y H:i') }} &mdash;
    {{ $tx->candidate->name ?? $tx->type }} &mdash;
    {{ number_format($tx->amount) }}
</p>
@endforeach
</div>
<div class="pd-card" style="grid-column:span 2">
<h2>Buy party tokens</h2>@foreach($packages as $package)<form method="POST" action="{{ route('party.tokens.purchase') }}" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px">

@csrf

<input type="hidden" name="package_id" value="{{ $package->id }}">
<strong>{{ $package->name }}: {{ number_format($package->token_amount) }} / KES {{ number_format($package->price) }}</strong>
<span>
</span>
<input name="phone" value="{{ auth()->user()->phone }}" placeholder="Payment phone" required>
<input type="email" name="email" value="{{ auth()->user()->email }}" required>
<button style="grid-column:span 2">Purchase</button>
</form>
@endforeach
</div>
</section>
<section class="pd-grid">
<div class="pd-card" style="grid-column:span 2">
<h2>Claim an existing aspirant</h2>
<form method="POST" action="{{ route('party.claims.store') }}" style="display:flex;gap:10px">

@csrf

<select name="candidate_id" required>
<option value="">Select aspirant</option>
@foreach($claimableCandidates as $candidate)
<option value="{{ $candidate->id }}">
    {{ $candidate->name }} &mdash;
    {{ $candidate->politicalParty->name ?? 'Unassigned' }}
</option>
@endforeach
</select>
<button>Submit claim</button>
</form>
@foreach($claims as $claim)
<p>
    {{ $claim->candidate->name ?? 'Candidate' }} &mdash;
    {{ ucfirst($claim->status) }}
</p>
@endforeach
</div>@if($membership->role==='party_admin')<div class="pd-card" style="grid-column:span 2">
<h2>Party officials</h2>
<form method="POST" action="{{ route('party.officials.store') }}" class="pd-grid">

@csrf

<input name="name" placeholder="Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Temporary password" required>
<select name="role">
<option value="party_staff">Staff</option>
<option value="party_admin">Admin</option>
</select>
<button>Add official</button>
</form>@foreach($officials as $official)<div style="display:flex;justify-content:space-between;margin-top:12px">
<span>
    {{ $official->name }} &mdash;
    {{ str($official->pivot->role)->headline() }}
</span>
@unless($official->is(auth()->user()))
<form method="POST" action="{{ route('party.officials.destroy', $official) }}">

@csrf


@method('DELETE')
<button class="danger">Remove</button>
</form>
@endunless
</div>
@endforeach
</div>
@endif
</section>
</div>
</main>
@endsection
