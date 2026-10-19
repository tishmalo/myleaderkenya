@extends('layouts.app')

@section('page_title', 'Dashboard Overview')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Total Users</p>
                    <p class="text-4xl font-semibold text-white mt-2">{{ $totalUsers }}</p>
                </div>
                <i class="fas fa-users text-5xl text-emerald-600/30"></i>
            </div>
        </div>

        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Registered Voters</p>
                    <p class="text-4xl font-semibold text-emerald-400 mt-2">{{ $voterStats['confirmedVoters'] ?? 0 }}</p>
                </div>
                <i class="fas fa-check-circle text-5xl text-emerald-600/30"></i>
            </div>
            <p class="text-emerald-400 text-sm mt-4">Avg age: {{ $voterStats['avgAge'] ?? 0 }}</p>
        </div>

        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Messages</p>
                    <p class="text-4xl font-semibold text-white mt-2">{{ $totalMessages }}</p>
                </div>
                <i class="fas fa-comment-dots text-5xl text-emerald-600/30"></i>
            </div>
        </div>

        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Groups</p>
                    <p class="text-4xl font-semibold text-white mt-2">{{ $totalGroups ?? 0 }}</p>
                </div>
                <i class="fas fa-users text-5xl text-emerald-600/30"></i>
            </div>
        </div>
    </div>

    <!-- Aspirant & Content Stats -->
    @php($content = $content ?? [])
    @php($aspirants = $content['aspirants'] ?? [])
    @php($links = $content['links'] ?? [])
    @php($parties = $content['parties'] ?? [])
    @php($polls = $content['polls'] ?? [])
    @php($news = $content['news'] ?? [])
    @php($events = $content['events'] ?? [])

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Aspirants</p>
                    <p class="text-4xl font-semibold text-white mt-2">{{ number_format($aspirants['approved'] ?? 0) }}</p>
                </div>
                <i class="fas fa-user-tie text-5xl text-emerald-600/30"></i>
            </div>
            <p class="text-emerald-400 text-sm mt-4">{{ number_format($aspirants['total'] ?? 0) }} total</p>
            @if(($aspirants['pending'] ?? 0) > 0)
                <a href="{{ url('/candidates?approval_status=pending') }}" class="text-amber-300 text-sm hover:underline">
                    {{ number_format($aspirants['pending']) }} awaiting approval
                </a>
            @endif
        </div>

        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Links</p>
                    <p class="text-4xl font-semibold text-white mt-2">{{ number_format($links['approved'] ?? 0) }}</p>
                </div>
                <i class="fas fa-link text-5xl text-emerald-600/30"></i>
            </div>
            <p class="text-zinc-400 text-sm mt-4">
                {{ number_format($links['total'] ?? 0) }} total &middot; {{ number_format($links['national'] ?? 0) }} national
            </p>
            @if(($links['pending'] ?? 0) > 0)
                <a href="{{ url('/admin/links?status=pending') }}" class="text-amber-300 text-sm hover:underline">
                    {{ number_format($links['pending']) }} awaiting review
                </a>
            @endif
        </div>

        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Political Parties</p>
                    <p class="text-4xl font-semibold text-white mt-2">{{ number_format($parties['published'] ?? 0) }}</p>
                </div>
                <i class="fas fa-landmark text-5xl text-emerald-600/30"></i>
            </div>
            <p class="text-zinc-400 text-sm mt-4">{{ number_format($parties['total'] ?? 0) }} total</p>
        </div>

        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Polls</p>
                    <p class="text-4xl font-semibold text-white mt-2">{{ number_format($polls['active'] ?? 0) }}</p>
                </div>
                <i class="fas fa-chart-simple text-5xl text-emerald-600/30"></i>
            </div>
            <p class="text-zinc-400 text-sm mt-4">{{ number_format($polls['total'] ?? 0) }} total</p>
        </div>
    </div>

    <!-- Aspirants by position / links by county -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3 mb-6">
                <h3 class="font-semibold flex items-center gap-3">
                    <i class="fas fa-id-badge text-emerald-500"></i> Aspirants by Position
                </h3>
                <a href="{{ url('/candidates') }}" class="text-sm text-emerald-400 hover:underline">Manage</a>
            </div>

            <ul class="space-y-3">
                @forelse(($aspirants['byPosition'] ?? collect()) as $position)
                    <li class="flex items-center justify-between gap-3 bg-zinc-950 rounded-2xl px-5 py-3">
                        <span class="font-medium">{{ $position['label'] }}</span>
                        <span class="flex items-center gap-3 text-sm">
                            @if($position['pending'] > 0)
                                <span class="text-amber-300">{{ number_format($position['pending']) }} pending</span>
                            @endif
                            <span class="px-4 py-1 bg-emerald-500/10 text-emerald-400 rounded-2xl font-semibold">
                                {{ number_format($position['approved']) }}
                            </span>
                        </span>
                    </li>
                @empty
                    <li class="text-zinc-500 text-sm">No aspirants have been added yet.</li>
                @endforelse

                @if(($aspirants['unassigned'] ?? 0) > 0)
                    <li class="flex items-center justify-between gap-3 bg-zinc-950 rounded-2xl px-5 py-3">
                        <span class="font-medium text-zinc-400">Position not set</span>
                        <span class="px-4 py-1 bg-zinc-800 text-zinc-300 rounded-2xl text-sm font-semibold">{{ number_format($aspirants['unassigned']) }}</span>
                    </li>
                @endif
            </ul>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3 mb-6">
                <h3 class="font-semibold flex items-center gap-3">
                    <i class="fas fa-link text-emerald-500"></i> Links by County
                </h3>
                <a href="{{ url('/admin/links') }}" class="text-sm text-emerald-400 hover:underline">Manage</a>
            </div>

            <ul class="space-y-3">
                @forelse(($links['byCounty'] ?? collect()) as $county)
                    <li class="flex items-center justify-between gap-3 bg-zinc-950 rounded-2xl px-5 py-3">
                        <span class="font-medium">{{ $county->name }}</span>
                        <span class="px-4 py-1 bg-emerald-500/10 text-emerald-400 rounded-2xl text-sm font-semibold">{{ number_format($county->total) }}</span>
                    </li>
                @empty
                    <li class="text-zinc-500 text-sm">No approved links yet.</li>
                @endforelse
            </ul>

            <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                <div class="bg-zinc-950 rounded-2xl px-5 py-3">
                    <p class="text-zinc-500">Stories published</p>
                    <p class="text-xl font-semibold text-white mt-1">{{ number_format($news['published'] ?? 0) }}</p>
                </div>
                <div class="bg-zinc-950 rounded-2xl px-5 py-3">
                    <p class="text-zinc-500">Events</p>
                    <p class="text-xl font-semibold text-white mt-1">{{ number_format($events['total'] ?? 0) }}</p>
                    @if(($events['pending'] ?? 0) > 0)
                        <p class="text-xs text-amber-300 mt-1">{{ number_format($events['pending']) }} pending</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <h3 class="font-semibold mb-6 flex items-center gap-3">
                <i class="fas fa-comment-dots text-emerald-500"></i> Recent Messages
            </h3>
            <div class="space-y-5 max-h-96 overflow-auto">
                @foreach($messages as $msg)
                <div class="flex gap-4 pb-5 border-b border-zinc-800 last:border-none">
                    <div class="w-10 h-10 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-400 text-xl">💬</div>
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <span class="font-medium">{{ $msg->username }}</span>
                            <span class="text-xs text-zinc-500">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-zinc-300 mt-1">{{ $msg->message }}</p>
                        <p class="text-xs text-zinc-500 mt-2 font-mono">
                            📍 {{ number_format($msg->latitude ?? 0, 5) }}, {{ number_format($msg->longitude ?? 0, 5) }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <h3 class="font-semibold mb-6 flex items-center gap-3">
                <i class="fas fa-map-marker-alt text-emerald-500"></i> Voters by County
            </h3>
            <ul class="space-y-4">
                @foreach($voterStats['byCounty'] ?? [] as $county)
                <li class="flex justify-between items-center bg-zinc-950 rounded-2xl px-5 py-4">
                    <span class="font-medium">{{ $county->county }}</span>
                    <span class="px-5 py-1 bg-emerald-500/10 text-emerald-400 rounded-2xl text-sm font-semibold">
                        {{ $county->count }}
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

</div>
@endsection