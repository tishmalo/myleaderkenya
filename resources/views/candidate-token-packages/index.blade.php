@extends('layouts.app')
@section('page_title', 'Token Packages')
@section('content')
<div class="max-w-7xl mx-auto">
<div class="flex justify-between items-center mb-8"><h1 class="text-3xl font-semibold text-white"><i class="fas fa-box text-emerald-500"></i> Token Packages</h1><a href="{{ route('candidate-token-packages.create') }}" class="bg-emerald-600 px-6 py-3 rounded-2xl">New Package</a></div>
@if(session('success'))<div class="mb-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 p-4 text-emerald-300">{{ session('success') }}</div>@endif
<div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden"><table class="w-full"><thead class="bg-zinc-950"><tr><th class="px-6 py-4 text-left">Package</th><th>Tokens</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead><tbody class="divide-y divide-zinc-800">@forelse($packages as $package)<tr><td class="px-6 py-4">{{ $package->name }}</td><td class="text-center">{{ number_format($package->token_amount) }}</td><td class="text-center">{{ $package->currency }} {{ number_format($package->price) }}</td><td class="text-center">{{ $package->is_active ? 'Active' : 'Inactive' }}</td><td class="text-center"><a href="{{ route('candidate-token-packages.edit', $package) }}" class="text-blue-400"><i class="fas fa-edit"></i></a></td></tr>@empty<tr><td colspan="5" class="text-center py-12 text-zinc-500">No packages found.</td></tr>@endforelse</tbody></table></div><div class="mt-8">{{ $packages->links() }}</div>
</div>
@endsection
