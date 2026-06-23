@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-semibold mb-8 flex items-center gap-3 text-white">
        <i class="fas fa-chart-pie text-emerald-500"></i> Voter Statistics
    </h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Total Registered Voters</p>
                    <p class="text-5xl font-bold text-white mt-4">{{ $totalVoters ?? 0 }}</p>
                </div>
                <i class="fas fa-users text-6xl text-emerald-600/30"></i>
            </div>
        </div>

        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Confirmed Voters</p>
                    <p class="text-5xl font-bold text-emerald-400 mt-4">{{ $voterStats['confirmedVoters'] ?? 0 }}</p>
                </div>
                <i class="fas fa-check-circle text-6xl text-emerald-600/30"></i>
            </div>
        </div>

        <div class="card bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-zinc-400 text-sm">Average Age</p>
                    <p class="text-5xl font-bold text-white mt-4">{{ $voterStats['avgAge'] ?? 0 }}</p>
                </div>
                <i class="fas fa-user-clock text-6xl text-emerald-600/30"></i>
            </div>
        </div>
    </div>

    <!-- Voters by County Section -->
    @if(isset($voterStats['byCounty']) && $voterStats['byCounty']->count() > 0)
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3 text-white">
            <i class="fas fa-map-marker-alt text-emerald-500"></i> Voters by County
        </h2>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr class="border-b border-zinc-800">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">County</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-400">Number of Voters</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @foreach($voterStats['byCounty'] as $county)
                <tr class="hover:bg-zinc-800/70 transition-colors">
                    <td class="px-6 py-5 font-medium text-white">{{ $county->county }}</td>
                    <td class="px-6 py-5">
                        <span class="text-2xl font-bold text-emerald-400">{{ $county->count }}</span>
                    </td>
                </tr>
                @endforeach

                @if($voterStats['byCounty']->count() === 0)
                <tr>
                    <td colspan="2" class="px-6 py-16 text-center text-zinc-500">
                        No county data available.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-12 text-center">
        <i class="fas fa-map-marker-alt text-6xl text-zinc-700 mb-4"></i>
        <p class="text-zinc-400">No county statistics available yet.</p>
    </div>
    @endif
</div>
@endsection