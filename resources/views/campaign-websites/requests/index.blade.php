@extends('layouts.app')

@section('page_title', 'Campaign Website Requests')

@section('content')
<div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3"><i class="fas fa-globe text-emerald-500"></i> Website Requests</h1>
        <a href="{{ route('campaign-website-samples.index') }}" class="bg-zinc-800 hover:bg-zinc-700 px-5 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-images mr-2"></i> Samples</a>
    </div>

    <form method="GET" action="{{ route('campaign-website-requests.index') }}" class="mb-6 flex gap-3">
        <select name="status" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
            <option value="">All Statuses</option>
            @foreach(['new' => 'New', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button class="bg-zinc-700 hover:bg-zinc-600 px-6 py-3 rounded-2xl text-sm font-medium">Filter</button>
    </form>

    <div class="grid gap-4">
        @forelse($requests as $requestItem)
            <article class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
                <div class="grid lg:grid-cols-[1fr_320px] gap-6">
                    <div>
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <h2 class="text-2xl font-semibold text-white">{{ $requestItem->candidate_name }}</h2>
                                <p class="text-zinc-400">{{ $requestItem->candidate->position->name ?? 'Aspirant' }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300">{{ str_replace('_', ' ', ucfirst($requestItem->status)) }}</span>
                        </div>
                        <div class="grid md:grid-cols-2 gap-3 text-sm text-zinc-300">
                            <div><span class="text-zinc-500">Phone:</span> {{ $requestItem->phone ?: '-' }}</div>
                            <div><span class="text-zinc-500">Email:</span> {{ $requestItem->email ?: '-' }}</div>
                            <div><span class="text-zinc-500">Domain:</span> {{ $requestItem->preferred_domain ?: '-' }}</div>
                            <div><span class="text-zinc-500">Package:</span> {{ ucfirst($requestItem->website_type) }}</div>
                        </div>
                        @if($requestItem->reference_url)
                            <p class="mt-4 text-sm"><span class="text-zinc-500">Reference:</span> <a href="{{ $requestItem->reference_url }}" target="_blank" class="text-emerald-400 hover:text-emerald-300">{{ $requestItem->reference_url }}</a></p>
                        @endif
                        @if($requestItem->notes)
                            <p class="mt-4 text-zinc-300 leading-relaxed">{{ $requestItem->notes }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('campaign-website-requests.update', $requestItem) }}" class="grid gap-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                            @foreach(['new' => 'New', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                                <option value="{{ $value }}" {{ $requestItem->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <textarea name="admin_notes" rows="4" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white" placeholder="Admin notes">{{ $requestItem->admin_notes }}</textarea>
                        <button class="bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl text-sm font-medium">Update Request</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-12 text-center text-zinc-500">No campaign website requests found.</div>
        @endforelse
    </div>

    <div class="mt-8">{{ $requests->appends(request()->query())->links() }}</div>
</div>
@endsection
