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