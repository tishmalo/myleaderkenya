@extends('layouts.app')
@section('page_title', 'Payment Transactions')
@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <h1 class="text-3xl font-semibold text-white"><i class="fas fa-receipt text-emerald-500"></i> Payment Transactions</h1>
        <form method="GET" action="{{ route('candidate-token-purchases.index') }}" class="flex gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input name="search" value="{{ request('search') }}" maxlength="120" placeholder="Name, email or reference" class="bg-zinc-900 border border-zinc-700 rounded-xl px-4 py-3 text-white">
            <button class="bg-emerald-600 hover:bg-emerald-500 rounded-xl px-5 py-3 font-semibold text-white">Search</button>
        </form>
    </div>
    <nav class="flex flex-wrap gap-2 mb-6" aria-label="Payment transaction types">
        @foreach(['candidate' => 'Aspirant Token Purchases', 'kitty' => 'Kitty Purchases', 'donations' => 'Aspirant Donations'] as $key => $label)
            <a href="{{ route('candidate-token-purchases.index', ['tab' => $key]) }}" class="px-5 py-3 rounded-xl border {{ $tab === $key ? 'bg-emerald-600 border-emerald-500 text-white' : 'bg-zinc-900 border-zinc-700 text-zinc-300' }}">{{ $label }}</a>
        @endforeach
    </nav>
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-x-auto">
        @if($tab === 'kitty')
            <table class="w-full min-w-[1050px]"><thead class="bg-zinc-950"><tr><th class="px-6 py-4 text-left">Purchaser</th><th>Kitty Type</th><th>Package</th><th>Tokens</th><th>Amount Paid</th><th>Status</th><th>Reference</th><th>Date</th></tr></thead>
            <tbody class="divide-y divide-zinc-800">@forelse($kittyPurchases as $purchase)<tr><td class="px-6 py-4"><strong>{{ $purchase->purchaser_name ?: ($purchase->user->name ?? '-') }}</strong><br><small class="text-zinc-400">{{ $purchase->user->email ?? '-' }}</small></td><td class="text-center">{{ $purchase->kittyType->name ?? \Illuminate\Support\Str::headline($purchase->kitty_type ?: '-') }}</td><td class="text-center">{{ $purchase->package_name }}</td><td class="text-center">{{ number_format($purchase->token_amount) }}</td><td class="text-center">{{ $purchase->currency }} {{ number_format($purchase->price, 2) }}</td><td class="text-center">{{ ucfirst($purchase->status) }}</td><td class="text-center">{{ $purchase->payment_reference ?: $purchase->checkout_reference }}</td><td class="text-center">{{ $purchase->created_at->format('d M Y, H:i') }}</td></tr>@empty<tr><td colspan="8" class="text-center py-12 text-zinc-500">No Kitty purchases found.</td></tr>@endforelse</tbody></table>
        @elseif($tab === 'donations')
            <table class="w-full min-w-[1200px]"><thead class="bg-zinc-950"><tr><th class="px-6 py-4 text-left">Supporter</th><th>Aspirant</th><th>Gross Paid</th><th>Platform Fee</th><th>Aspirant Amount</th><th>Status</th><th>Reference</th><th>Date</th></tr></thead>
            <tbody class="divide-y divide-zinc-800">@forelse($donations as $donation)<tr><td class="px-6 py-4"><strong>{{ $donation->supporter_name }}</strong><br><small class="text-zinc-400">{{ $donation->supporter_email }}</small></td><td class="text-center">{{ $donation->candidate->name ?? '-' }}</td><td class="text-center">{{ $donation->currency }} {{ number_format($donation->gross_amount, 2) }}</td><td class="text-center">{{ $donation->currency }} {{ number_format($donation->platform_fee_amount, 2) }} <small class="text-zinc-400">({{ number_format($donation->platform_fee_rate, 2) }}%)</small></td><td class="text-center">{{ $donation->currency }} {{ number_format($donation->aspirant_amount, 2) }}</td><td class="text-center">{{ ucfirst($donation->status) }}</td><td class="text-center">{{ $donation->payment_reference ?: $donation->checkout_reference }}</td><td class="text-center">{{ ($donation->paid_at ?: $donation->created_at)->format('d M Y, H:i') }}</td></tr>@empty<tr><td colspan="8" class="text-center py-12 text-zinc-500">No aspirant donations found.</td></tr>@endforelse</tbody></table>
        @else
            <table class="w-full min-w-[900px]"><thead class="bg-zinc-950"><tr><th class="px-6 py-4 text-left">Candidate</th><th>Package</th><th>Tokens</th><th>Amount</th><th>Status</th><th>Reference</th><th>Date</th></tr></thead><tbody class="divide-y divide-zinc-800">@forelse($purchases as $purchase)<tr><td class="px-6 py-4">{{ $purchase->candidate->name ?? '-' }}</td><td class="text-center">{{ $purchase->package_name }}</td><td class="text-center">{{ number_format($purchase->token_amount) }}</td><td class="text-center">{{ $purchase->currency }} {{ number_format($purchase->price, 2) }}</td><td class="text-center">{{ ucfirst($purchase->status ?? 'pending') }}</td><td class="text-center">{{ $purchase->payment_reference ?: $purchase->checkout_reference ?: '-' }}</td><td class="text-center">{{ $purchase->created_at->format('d M Y, H:i') }}</td></tr>@empty<tr><td colspan="7" class="text-center py-12 text-zinc-500">No aspirant token purchases found.</td></tr>@endforelse</tbody></table>
        @endif
    </div>
    <div class="mt-8">@if($tab === 'kitty') {{ $kittyPurchases->links() }} @elseif($tab === 'donations') {{ $donations->links() }} @else {{ $purchases->links() }} @endif</div>
</div>
@endsection
