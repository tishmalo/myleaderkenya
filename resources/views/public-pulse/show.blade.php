@extends('layouts.app')

@section('page_title', 'Pulse Job Details')

@section('content')
@php
    $statusClass = $job->status === 'completed'
        ? 'bg-emerald-500/15 text-emerald-300'
        : (in_array($job->status, ['failed', 'submission_failed'], true)
            ? 'bg-red-500/15 text-red-300'
            : 'bg-amber-500/15 text-amber-300');
    $items = data_get($tweets, 'items', []);
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('public-pulse.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-400 hover:text-emerald-300"><i class="fas fa-arrow-left"></i> All jobs</a>
            <h1 class="mt-3 text-3xl font-semibold text-white">{{ $job->candidate?->name }} Pulse Job</h1>
            <p class="mt-1 break-all font-mono text-xs text-zinc-500">{{ $job->job_ref }}</p>
        </div>
        <div class="flex gap-2">
            @if(!$job->isTerminal() && $job->engine_job_id)
                <form method="POST" action="{{ route('public-pulse.jobs.sync', $job) }}">@csrf @method('PATCH')<button class="rounded-xl bg-zinc-800 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-700" data-loading-label="Polling..."><i class="fas fa-rotate mr-2"></i>Poll now</button></form>
            @elseif(!$job->engine_job_id)
                <form method="POST" action="{{ route('public-pulse.jobs.retry', $job) }}">@csrf<button class="rounded-xl bg-amber-600 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-500" data-loading-label="Retrying..."><i class="fas fa-rotate mr-2"></i>Retry submission</button></form>
            @endif
        </div>
    </div>

    @if(session('success'))<div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>@endif
    @if($job->error_message)<div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-300">{{ $job->error_message }}</div>@endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5"><div class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Status</div><div class="mt-3"><span class="rounded-full px-3 py-1 text-sm font-semibold {{ $statusClass }}">{{ str_replace('_', ' ', $job->status) }}</span></div></div>
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5"><div class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Result type</div><div class="mt-2 text-xl font-semibold text-white">{{ $job->partial ? 'Partial' : 'Full' }}</div></div>
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5"><div class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Requested</div><div class="mt-2 text-xl font-semibold text-white">{{ number_format($job->requested_limit) }}</div><div class="mt-1 text-xs text-zinc-500">mentions</div></div>
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5"><div class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Completed</div><div class="mt-2 text-base font-semibold text-white">{{ $job->completed_at?->format('d M Y, H:i') ?: 'Not completed' }}</div></div>
    </div>

    <section class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5">
        <div class="flex items-center justify-between gap-3"><div><h2 class="text-lg font-semibold text-white">Analysis summary</h2><p class="mt-1 text-xs text-zinc-500">Summary data is stored in Laravel; raw posts remain in Pulse Engine.</p></div>@if($job->summary)<span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">Ready</span>@endif</div>
        @if($job->summary)
            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl bg-zinc-950/70 p-4"><div class="text-xs uppercase text-zinc-500">Pulse score</div><x-pulse-score :score="data_get($job->summary, 'pulse_score')" /></div>
                <div class="rounded-xl bg-zinc-950/70 p-4"><div class="text-xs uppercase text-zinc-500">Mentions</div><div class="mt-2 text-2xl font-semibold text-white">{{ number_format((int) data_get($job->summary, 'total_mentions', 0)) }}</div></div>
                <div class="rounded-xl bg-zinc-950/70 p-4"><div class="text-xs uppercase text-zinc-500">Unique authors</div><div class="mt-2 text-2xl font-semibold text-white">{{ number_format((int) data_get($job->summary, 'unique_authors', 0)) }}</div></div>
                <div class="rounded-xl bg-zinc-950/70 p-4"><div class="text-xs uppercase text-zinc-500">Confidence</div><div class="mt-2 text-2xl font-semibold capitalize text-white">{{ data_get($job->summary, 'overall_confidence', 'N/A') }}</div>@if(data_get($job->summary, 'overall_confidence') === 'low')<div class="mt-2 text-xs text-amber-300">Interpret cautiously: the sample is below the confidence threshold.</div>@endif</div>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                @foreach(['positive' => 'emerald', 'neutral' => 'zinc', 'negative' => 'red'] as $sentiment => $color)
                    <div class="rounded-xl border border-zinc-800 bg-zinc-950/50 p-4"><div class="text-sm capitalize text-zinc-400">{{ $sentiment }}</div><div class="mt-1 text-xl font-semibold text-white"><x-pulse-percentage :value="data_get($job->summary, 'sentiment.'.$sentiment, 0)" /></div></div>
                @endforeach
            </div>
        @else
            <div class="mt-5 rounded-xl border border-dashed border-zinc-700 bg-zinc-950/40 px-5 py-10 text-center"><i class="fas fa-hourglass-half text-2xl text-zinc-600"></i><p class="mt-3 font-semibold text-zinc-300">Analysis is not ready yet</p><p class="mt-1 text-sm text-zinc-500">This job is {{ str_replace('_', ' ', $job->status) }}. Poll it after the worker has processed the scrape.</p></div>
        @endif
    </section>

    <section class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="text-lg font-semibold text-white">Raw mentions</h2><p class="mt-1 text-xs text-zinc-500">Proxied live from Pulse Engine and never persisted in Laravel.</p></div><form class="flex gap-2"><select name="sentiment" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white"><option value="">All sentiment</option>@foreach(['positive','neutral','negative'] as $sentiment)<option value="{{ $sentiment }}" @selected(($filters['sentiment'] ?? null) === $sentiment)>{{ ucfirst($sentiment) }}</option>@endforeach</select><button class="rounded-xl bg-zinc-800 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-700">Filter</button></form></div>

        <div class="mt-5 space-y-3">
            @forelse($items as $tweet)
                @php($label = data_get($tweet, 'sentiment_label'))
                <article class="rounded-xl border border-zinc-800 bg-zinc-950/50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2"><div class="font-semibold text-white">&#64;{{ data_get($tweet, 'author', 'unknown') }}</div><time class="text-xs text-zinc-500">{{ data_get($tweet, 'posted_at', '') }}</time></div>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-300">{{ data_get($tweet, 'content', '') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-zinc-500"><span class="rounded-full bg-zinc-800 px-2 py-1">{{ $label ? ucfirst($label) : 'Unclassified' }}</span><span><i class="fas fa-heart mr-1"></i>{{ data_get($tweet, 'likes', 0) }}</span><span><i class="fas fa-retweet mr-1"></i>{{ data_get($tweet, 'retweets', 0) }}</span><span><i class="fas fa-comment mr-1"></i>{{ data_get($tweet, 'replies', 0) }}</span></div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-700 bg-zinc-950/40 px-5 py-10 text-center"><i class="fas fa-comments text-2xl text-zinc-600"></i><p class="mt-3 font-semibold text-zinc-300">No raw mentions available</p><p class="mt-1 text-sm text-zinc-500">{{ $job->isTerminal() ? 'No posts matched this job and filter.' : 'Mentions will appear here as soon as processing completes.' }}</p></div>
            @endforelse
        </div>
        @if($tweets && data_get($tweets, 'total', 0) > 0)<div class="mt-4 text-xs text-zinc-500">Showing {{ count($items) }} of {{ number_format((int) data_get($tweets, 'total')) }} mentions.</div>@endif
    </section>
</div>
@endsection


