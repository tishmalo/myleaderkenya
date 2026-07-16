@extends('layouts.app')

@section('page_title', 'Donors & Payments')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-semibold">Donors & Contributions</h1>
            <p class="text-zinc-400 mt-1">Track all donations and payments</p>
        </div>
        <a href="{{ route('donors.create') }}" 
           class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl flex items-center gap-3 transition-colors">
            <i class="fas fa-plus"></i>
            <span>Add New Donor</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-400 text-sm">Total Donors</p>
                    <p class="text-4xl font-semibold mt-2">{{ $totalDonors ?? \App\Models\Donor::count() }}</p>
                </div>
                <i class="fas fa-users text-5xl text-emerald-500 opacity-20"></i>
            </div>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-400 text-sm">Total Raised</p>
                    <p class="text-4xl font-semibold mt-2">KES {{ number_format($totalAmount ?? 0, 2) }}</p>
                </div>
                <i class="fas fa-hand-holding-heart text-5xl text-emerald-500 opacity-20"></i>
            </div>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-400 text-sm">Avg Donation</p>
                    <p class="text-4xl font-semibold mt-2">
                        KES {{ $totalDonors > 0 ? number_format(($totalAmount ?? 0) / $totalDonors, 0) : 0 }}
                    </p>
                </div>
                <i class="fas fa-chart-line text-5xl text-emerald-500 opacity-20"></i>
            </div>
        </div>
    </div>

    <!-- Recent Donations -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="px-8 py-5 border-b border-zinc-800 flex items-center justify-between">
            <h2 class="text-xl font-semibold">Recent Donations</h2>
            <a href="{{ route('donors.index') }}" class="text-emerald-400 hover:text-emerald-500 text-sm flex items-center gap-1">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="w-full max-w-full overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-zinc-800">
                        <th class="px-8 py-4 text-left text-zinc-400 font-medium">Donor</th>
                        <th class="px-8 py-4 text-left text-zinc-400 font-medium">Payment Method</th>
                        <th class="px-8 py-4 text-right text-zinc-400 font-medium">Amount</th>
                        <th class="px-8 py-4 text-left text-zinc-400 font-medium">Date</th>
                        <th class="px-8 py-4 text-center text-zinc-400 font-medium">Status</th>
                        <th class="px-8 py-4 w-24"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @foreach($recentDonors ?? [] as $donor)
                    <tr class="hover:bg-zinc-800/50 transition-colors">
                        <td class="px-8 py-5">
                            <div>
                                <p class="font-medium">{{ $donor->name }}</p>
                                @if($donor->email)
                                    <p class="text-sm text-zinc-500">{{ $donor->email }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-zinc-800 rounded-2xl text-sm">
                                <i class="fas fa-credit-card"></i>
                                {{ ucfirst(str_replace('_', ' ', $donor->payment_method)) }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right font-semibold text-emerald-400">
                            KES {{ number_format($donor->amount, 2) }}
                        </td>
                        <td class="px-8 py-5 text-zinc-400">
                            {{ $donor->created_at->format('d M, Y') }}
                        </td>
                        <td class="px-8 py-5 text-center">
                            @if($donor->status === 'completed')
                                <span class="px-4 py-1 bg-emerald-500/10 text-emerald-400 text-xs font-medium rounded-2xl">Completed</span>
                            @elseif($donor->status === 'pending')
                                <span class="px-4 py-1 bg-amber-500/10 text-amber-400 text-xs font-medium rounded-2xl">Pending</span>
                            @else
                                <span class="px-4 py-1 bg-red-500/10 text-red-400 text-xs font-medium rounded-2xl">{{ ucfirst($donor->status) }}</span>
                            @endif
                        </td>
                        <td class="px-8 py-5 text-right">
                            <a href="{{ route('donors.edit', $donor) }}" 
                               class="text-zinc-400 hover:text-white transition-colors">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach

                    @if(empty($recentDonors))
                    <tr>
                        <td colspan="6" class="px-8 py-12 text-center text-zinc-500">
                            No donations recorded yet.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
