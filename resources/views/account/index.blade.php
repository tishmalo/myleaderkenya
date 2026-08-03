@extends('layouts.landing')
@section('title', 'My Account - My Leader Kenya')
@section('content')
@include('components.frontend-nav')
<main class="min-h-screen bg-zinc-950 px-5 py-16 text-white">
<div class="mx-auto max-w-6xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div><p class="text-xs font-black uppercase tracking-[.25em] text-emerald-400">My Account</p><h1 class="mt-2 text-4xl font-bold">Welcome, {{ auth()->user()->name }}</h1><p class="mt-2 text-zinc-400">Manage aspirant submissions, claims, and approved dashboards.</p></div>
        <a href="{{ route('aspirants.register') }}" class="rounded-xl bg-emerald-600 px-5 py-3 font-bold">Submit or claim an aspirant</a>
    </div>
    @if(session('success'))<div class="mt-6 rounded-xl border border-emerald-700 bg-emerald-950 p-4 text-emerald-200">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-6 rounded-xl border border-red-700 bg-red-950 p-4 text-red-200">{{ $errors->first() }}</div>@endif

    <section class="mt-10"><h2 class="text-2xl font-bold">Available dashboards</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
        @forelse($candidates as $candidate)
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5"><h3 class="text-xl font-bold">{{ $candidate->name }}</h3><p class="mt-1 text-sm text-zinc-400">{{ $candidate->position?->name }} @if($candidate->politicalParty) · {{ $candidate->politicalParty->name }} @endif</p><form method="POST" action="{{ route('my-account.aspirants.select') }}" class="mt-4">@csrf<input type="hidden" name="candidate_id" value="{{ $candidate->id }}"><button class="rounded-xl bg-red-600 px-4 py-2 font-bold">Login to Dashboard</button></form></div>
        @empty <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6 text-zinc-400">No approved aspirant dashboards yet.</div> @endforelse
        </div>
    </section>

    <section class="mt-10"><h2 class="text-2xl font-bold">Claim requests</h2><div class="mt-4 overflow-hidden rounded-2xl border border-zinc-800">
        @forelse($claims as $claim)<div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-800 bg-zinc-900 p-5 last:border-0"><div><strong>{{ $claim->candidate?->name ?? 'Aspirant' }}</strong><p class="text-sm text-zinc-400">{{ str($claim->relationship)->headline() }}</p></div><span class="rounded-full px-3 py-1 text-sm font-bold {{ $claim->status === 'approved' ? 'bg-emerald-950 text-emerald-300' : ($claim->status === 'rejected' ? 'bg-red-950 text-red-300' : 'bg-amber-950 text-amber-300') }}">{{ ucfirst($claim->status) }}</span></div>
        @empty <div class="bg-zinc-900 p-6 text-zinc-400">You have not submitted any claims.</div> @endforelse
    </div></section>
</div></main>
@endsection
