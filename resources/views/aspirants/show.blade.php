@extends('layouts.landing')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-16">
    <a href="{{ route('news.public') }}" class="inline-flex items-center gap-2 text-emerald-400 mb-8">
        ← Back to News
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        <!-- Left Profile -->
        <div class="lg:col-span-4">
            <div class="sticky top-8">
                @if($candidate->profile_picture)
                    <img src="{{ Storage::url($candidate->profile_picture) }}" 
                         class="w-full aspect-square object-cover rounded-3xl">
                @else
                    <div class="w-full aspect-square bg-zinc-800 rounded-3xl flex items-center justify-center text-6xl">
                        👤
                    </div>
                @endif

                <div class="mt-8 bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
                    <h1 class="text-4xl font-bold">{{ $candidate->name }}</h1>
                    @if($candidate->nick_name)
                        <p class="text-emerald-400 text-2xl mt-1">"{{ $candidate->nick_name }}"</p>
                    @endif

                    <p class="mt-6 text-xl font-medium">{{ $candidate->position->name ?? 'Aspirant' }}</p>
                    
                    @if($candidate->politicalParty)
                        <p class="mt-2 text-zinc-400">Political Party: <span class="text-white">{{ $candidate->politicalParty->name }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Content -->
        <div class="lg:col-span-8">
            <div class="prose prose-invert max-w-none">
                {!! nl2br($candidate->about) !!}
            </div>

            <div class="mt-12 grid grid-cols-2 gap-6 text-sm">
                @if($candidate->county)
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6">
                    <p class="text-zinc-400">County</p>
                    <p class="text-xl font-medium">{{ $candidate->county }}</p>
                </div>
                @endif
                @if($candidate->constituency)
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6">
                    <p class="text-zinc-400">Constituency</p>
                    <p class="text-xl font-medium">{{ $candidate->constituency }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection