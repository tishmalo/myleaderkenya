@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-white">X Sessions</h1>
            <p class="mt-1 text-sm text-zinc-400">Manage manually supplied X/Twitter sessions for Public Pulse scrapers.</p>
        </div>
        <a href="{{ route('public-pulse.index') }}" class="rounded-xl border border-zinc-700 px-4 py-2 text-sm font-semibold text-zinc-300 hover:text-white">Mentions</a>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-300">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('public-pulse.x-sessions.store') }}" class="grid gap-4 rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 md:grid-cols-2">
        @csrf
        <div>
            <label class="mb-2 block text-sm text-zinc-400">Label</label>
            <input name="label" required class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-white" placeholder="X Session 1">
        </div>
        <div>
            <label class="mb-2 block text-sm text-zinc-400">Provider</label>
            <select name="provider" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-white">
                <option value="x_twscrape">twscrape</option>
                <option value="x_nitter">Nitter</option>
                <option value="x_snscrape">snscrape</option>
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm text-zinc-400">Username</label>
            <input name="username" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-white" placeholder="@account">
        </div>
        <div>
            <label class="mb-2 block text-sm text-zinc-400">Notes</label>
            <input name="notes" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-white" placeholder="Purpose or source pool">
        </div>
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm text-zinc-400">Cookie/session JSON</label>
            <textarea name="encrypted_session_payload" required rows="7" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-xs text-white" placeholder='{"cookies":[{"name":"auth_token","value":"..."},{"name":"ct0","value":"..."}]}'></textarea>
        </div>
        <div class="md:col-span-2">
            <button class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-500">Save And Check Health</button>
        </div>
    </form>

    <form method="GET" action="{{ route('public-pulse.x-sessions.index') }}" class="grid gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/60 p-4 md:grid-cols-5">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search sessions" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white md:col-span-2">
        <select name="provider" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            <option value="">All providers</option>
            @foreach(['x_twscrape', 'x_nitter', 'x_snscrape'] as $provider)
                <option value="{{ $provider }}" @selected(($filters['provider'] ?? '') === $provider)>{{ $provider }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            <option value="">All statuses</option>
            @foreach(\App\Models\PublicPulseSourceAccount::STATUSES as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str_replace('_', ' ', $status) }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-zinc-800 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-700">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-zinc-800 bg-zinc-900/60">
        <table class="min-w-full divide-y divide-zinc-800 text-sm">
            <thead class="bg-zinc-950/70 text-left text-xs uppercase tracking-wider text-zinc-500">
                <tr>
                    <th class="px-4 py-3">Session</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Last Check</th>
                    <th class="px-4 py-3">Failures</th>
                    <th class="px-4 py-3">Issue</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 text-zinc-300">
                @forelse($accounts as $account)
                    @php($healthy = $account->status === \App\Models\PublicPulseSourceAccount::STATUS_HEALTHY)
                    <tr>
                        <td class="px-4 py-4 align-top">
                            <div class="font-semibold text-white">{{ $account->label }}</div>
                            <div class="text-xs text-zinc-500">{{ $account->provider }} @if($account->username) · {{ $account->username }} @endif</div>
                        </td>
                        <td class="px-4 py-4 align-top">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $healthy ? 'bg-emerald-500/15 text-emerald-300' : 'bg-red-500/15 text-red-300' }}">
                                {{ str_replace('_', ' ', $account->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 align-top">{{ $account->last_health_check_at?->format('d M Y H:i') ?? 'Never' }}</td>
                        <td class="px-4 py-4 align-top">{{ $account->consecutive_failure_count }} / {{ $account->failure_count }}</td>
                        <td class="max-w-sm px-4 py-4 align-top text-xs text-zinc-400">{{ $account->last_error_message ?: 'No issue recorded.' }}</td>
                        <td class="px-4 py-4 align-top">
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('public-pulse.x-sessions.check', $account) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-lg border border-zinc-700 px-3 py-2 text-xs font-semibold text-zinc-300 hover:text-white">Check</button>
                                </form>
                                <form method="POST" action="{{ route('public-pulse.x-sessions.replace', $account) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-lg border border-red-500/40 px-3 py-2 text-xs font-semibold text-red-300 hover:text-red-200">Remove From Pool</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-zinc-500">No X sessions saved yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $accounts->links() }}</div>
</div>
@endsection
