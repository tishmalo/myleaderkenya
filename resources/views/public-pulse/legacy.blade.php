@extends('layouts.app')

@section('page_title', 'Legacy Mentions')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('public-pulse.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-400 hover:text-emerald-300"><i class="fas fa-arrow-left"></i> Engine jobs</a>
            <h1 class="mt-3 text-3xl font-semibold text-white">Legacy Mentions</h1>
            <p class="mt-1 text-sm text-zinc-400">Read-only historical Laravel data. Reclassification and Laravel NLP calls are disabled.</p>
        </div>
        <a href="{{ route('public-pulse.x-sessions.index') }}" class="inline-flex items-center rounded-xl border border-zinc-700 px-4 py-2 text-sm font-semibold text-zinc-300 hover:border-zinc-600 hover:text-white"><i class="fas fa-key mr-2"></i>X Sessions</a>
    </div>

    <div class="rounded-2xl border border-amber-500/25 bg-amber-500/10 px-5 py-4 text-sm text-amber-200">
        <i class="fas fa-lock mr-2"></i>These records are retained for reference only. New scraping and analysis happens in Pulse Engine.
    </div>

    <form method="GET" action="{{ route('public-pulse.legacy') }}" class="grid gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/60 p-4 sm:grid-cols-2 xl:grid-cols-6">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search mention text" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-3 text-sm text-white placeholder:text-zinc-600 xl:col-span-2">
        <input name="language" value="{{ $filters['language'] ?? '' }}" placeholder="Language" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-3 text-sm text-white placeholder:text-zinc-600">
        <input name="tone" value="{{ $filters['tone'] ?? '' }}" placeholder="Tone" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-3 text-sm text-white placeholder:text-zinc-600">
        <input name="sentiment" value="{{ $filters['sentiment'] ?? '' }}" placeholder="Sentiment" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-3 text-sm text-white placeholder:text-zinc-600">
        <div class="flex gap-2"><input name="topic" value="{{ $filters['topic'] ?? '' }}" placeholder="Topic" class="min-w-0 flex-1 rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-3 text-sm text-white placeholder:text-zinc-600"><button class="rounded-xl bg-zinc-800 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-700">Filter</button></div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-zinc-800 bg-zinc-900/60">
        <table class="min-w-full divide-y divide-zinc-800 text-sm">
            <thead class="bg-zinc-950/70 text-left text-xs uppercase tracking-wider text-zinc-500"><tr><th class="px-4 py-3">Source</th><th class="px-4 py-3">Text</th><th class="px-4 py-3">Language</th><th class="px-4 py-3">Tone</th><th class="px-4 py-3">Sentiment</th></tr></thead>
            <tbody class="divide-y divide-zinc-800 text-zinc-300">
                @forelse($mentions as $mention)
                    <tr class="hover:bg-zinc-800/30"><td class="whitespace-nowrap px-4 py-4 text-zinc-400">{{ $mention->source ?: 'Unknown' }}</td><td class="max-w-2xl px-4 py-4 leading-6 text-white">{{ $mention->text }}</td><td class="px-4 py-4">{{ $mention->language ?: 'Unknown' }}</td><td class="px-4 py-4">{{ $mention->tone ?: 'Unclassified' }}</td><td class="px-4 py-4"><span class="rounded-full bg-zinc-800 px-3 py-1 text-xs">{{ $mention->sentiment ?: 'Unclassified' }}</span></td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-14 text-center"><i class="fas fa-box-archive mb-3 block text-3xl text-zinc-700"></i><span class="text-zinc-500">No legacy mentions found.</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $mentions->withQueryString()->links() }}</div>
</div>
@endsection
