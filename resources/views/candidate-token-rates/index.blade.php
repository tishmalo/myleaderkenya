@extends('layouts.app')
@section('page_title', 'Token Rates')
@section('content')
<div class="max-w-7xl mx-auto">
<div class="flex justify-between items-center mb-8"><h1 class="text-3xl font-semibold text-white"><i class="fas fa-sliders text-emerald-500"></i> Token Rates</h1><a href="{{ route('candidate-token-rates.create') }}" class="bg-emerald-600 px-6 py-3 rounded-2xl">New Rate</a></div>
@if(session('success'))<div class="mb-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 p-4 text-emerald-300">{{ session('success') }}</div>@endif
<div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden"><table class="w-full"><thead class="bg-zinc-950"><tr><th class="px-6 py-4 text-left">Action</th><th>Calculation</th><th>Tokens</th><th>Status</th><th>Actions</th></tr></thead><tbody class="divide-y divide-zinc-800">@forelse($rates as $rate)<tr><td class="px-6 py-4"><p class="font-medium">{{ $rate->label }}</p><p class="text-xs text-zinc-500">{{ $rate->action_key }}</p></td><td class="text-center">{{ str_replace('_', ' ', $rate->calculation_type) }}</td><td class="text-center">{{ number_format($rate->token_amount) }}</td><td class="text-center">{{ $rate->is_active ? 'Active' : 'Inactive' }}</td><td class="text-center"><a href="{{ route('candidate-token-rates.edit', $rate) }}" class="text-blue-400"><i class="fas fa-edit"></i></a></td></tr>@empty<tr><td colspan="5" class="text-center py-12 text-zinc-500">No rates found.</td></tr>@endforelse</tbody></table></div><div class="mt-8">{{ $rates->links() }}</div>
</div>
@endsection
