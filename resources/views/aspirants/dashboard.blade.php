@extends('layouts.aspirant')

@section('page_title', 'Aspirant Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <p class="text-sm text-zinc-400">{{ $candidate->position->name ?? 'Aspirant' }}</p>
                <h1 class="text-3xl font-semibold text-white mt-1">{{ $candidate->name }}</h1>
                <p class="text-zinc-400 mt-2">{{ $candidate->politicalParty->name ?? 'Independent' }} @if($candidate->county) • {{ $candidate->county }} @endif @if($candidate->constituency) • {{ $candidate->constituency }} @endif @if($candidate->ward) • {{ $candidate->ward }} @endif</p>
            </div>
            <span class="px-4 py-2 rounded-2xl text-sm font-semibold {{ $candidate->approval_status === 'approved' ? 'bg-emerald-500/10 text-emerald-400' : ($candidate->approval_status === 'rejected' ? 'bg-red-500/10 text-red-400' : 'bg-amber-500/10 text-amber-300') }}">
                {{ ucfirst($candidate->approval_status ?? 'pending') }}
            </span>
        </div>
    </div>

    @if(($candidate->approval_status ?? 'pending') === 'pending')
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-3xl p-8 text-amber-100">
            Your aspirant account is awaiting admin approval. Campaign tools will unlock once approved.
        </div>
    @elseif($candidate->approval_status === 'rejected')
        <div class="bg-red-500/10 border border-red-500/30 rounded-3xl p-8 text-red-100">
            Your aspirant account was not approved. Please contact the administrator for assistance.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($campaignTools as $tool)
                <a href="{{ route('campaign-tools.show', $tool->slug) }}" class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden hover:border-emerald-600 transition-colors">
                    @if($tool->featured_image)
                        <img src="{{ Storage::url($tool->featured_image) }}" class="w-full h-40 object-cover" alt="{{ $tool->title }}">
                    @endif
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-white">{{ $tool->title }}</h2>
                        <p class="text-zinc-400 mt-3 text-sm">{{ $tool->excerpt }}</p>
                        <p class="text-emerald-400 mt-5 font-medium">Open tool</p>
                    </div>
                </a>
            @empty
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 text-zinc-400">No campaign tools are published yet.</div>
            @endforelse
        </div>
    @endif
</div>
@endsection

