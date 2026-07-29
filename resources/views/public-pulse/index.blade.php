@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-white">Public Pulse</h1>
            <p class="mt-1 text-sm text-zinc-400">Inspect language, tone, sentiment, topics, and classifier confidence for collected public mentions.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('public-pulse.index') }}" class="grid gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/60 p-4 md:grid-cols-6">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search mention or candidate" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white md:col-span-2">

        <select name="language" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            <option value="">All languages</option>
            @foreach(['en', 'sw', 'sheng', 'mixed', 'unknown'] as $language)
                <option value="{{ $language }}" @selected(($filters['language'] ?? '') === $language)>{{ strtoupper($language) }}</option>
            @endforeach
        </select>

        <select name="sentiment" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            <option value="">All sentiment</option>
            @foreach(['positive', 'neutral', 'negative', 'mixed'] as $sentiment)
                <option value="{{ $sentiment }}" @selected(($filters['sentiment'] ?? '') === $sentiment)>{{ ucfirst($sentiment) }}</option>
            @endforeach
        </select>

        <select name="tone" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            <option value="">All tones</option>
            @foreach(['supportive', 'critical', 'mocking', 'angry', 'concerned', 'informational', 'campaigning', 'attack', 'unclear'] as $tone)
                <option value="{{ $tone }}" @selected(($filters['tone'] ?? '') === $tone)>{{ ucfirst(str_replace('_', ' ', $tone)) }}</option>
            @endforeach
        </select>

        <button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Filter</button>

        <input type="text" name="topic" value="{{ $filters['topic'] ?? '' }}" placeholder="Topic, e.g. economy" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white md:col-span-2">
        <label class="flex items-center gap-2 rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-300">
            <input type="checkbox" name="low_confidence" value="1" @checked(! empty($filters['low_confidence']))>
            Low confidence
        </label>
        <a href="{{ route('public-pulse.index') }}" class="rounded-xl border border-zinc-700 px-4 py-2 text-center text-sm font-semibold text-zinc-300 hover:text-white">Clear</a>
    </form>

    <form method="POST" action="{{ route('public-pulse.reclassify') }}" class="rounded-2xl border border-zinc-800 bg-zinc-900/60">
        @csrf
        <div class="flex items-center justify-between border-b border-zinc-800 px-4 py-3">
            <div class="text-sm text-zinc-400">{{ $mentions->total() }} mentions</div>
            <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Reclassify Selected</button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-800 text-sm">
                <thead class="bg-zinc-950/70 text-left text-xs uppercase tracking-wider text-zinc-500">
                    <tr>
                        <th class="px-4 py-3"><input type="checkbox" data-select-all></th>
                        <th class="px-4 py-3">Mention</th>
                        <th class="px-4 py-3">Candidate</th>
                        <th class="px-4 py-3">Language</th>
                        <th class="px-4 py-3">Sentiment</th>
                        <th class="px-4 py-3">Tone</th>
                        <th class="px-4 py-3">Stance</th>
                        <th class="px-4 py-3">Topics</th>
                        <th class="px-4 py-3">Confidence</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800 text-zinc-300">
                    @forelse($mentions as $mention)
                        @php($classification = $mention->classification)
                        <tr>
                            <td class="px-4 py-4 align-top"><input type="checkbox" name="mentions[]" value="{{ $mention->id }}" data-row-check></td>
                            <td class="max-w-xl px-4 py-4 align-top">
                                <div class="font-semibold text-white">{{ $mention->title ?: Str::limit($mention->text, 90) }}</div>
                                <div class="mt-1 text-xs text-zinc-500">{{ $mention->source_key }} @if($mention->published_at) · {{ $mention->published_at->format('d M Y H:i') }} @endif</div>
                                @if($classification?->translated_summary)
                                    <div class="mt-2 rounded-xl border border-zinc-800 bg-zinc-950 p-3 text-xs text-zinc-300">{{ $classification->translated_summary }}</div>
                                @endif
                                @if($mention->url)
                                    <a href="{{ $mention->url }}" target="_blank" class="mt-2 inline-block text-xs text-emerald-400 hover:text-emerald-300">Open source</a>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-top text-white">{{ $mention->candidate?->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-4 align-top">{{ strtoupper($mention->language ?? 'unknown') }}</td>
                            <td class="px-4 py-4 align-top">{{ ucfirst($mention->sentiment ?? 'unclassified') }}</td>
                            <td class="px-4 py-4 align-top">{{ ucfirst(str_replace('_', ' ', $mention->tone ?? 'unclassified')) }}</td>
                            <td class="px-4 py-4 align-top">{{ ucfirst(str_replace('_', ' ', $classification->stance ?? 'unknown')) }}</td>
                            <td class="px-4 py-4 align-top">
                                <div class="flex flex-wrap gap-1">
                                    @foreach(($classification->topics ?? []) as $topic)
                                        <span class="rounded-full bg-zinc-800 px-2 py-1 text-xs">{{ str_replace('_', ' ', $topic) }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top">{{ $mention->classification_confidence !== null ? number_format($mention->classification_confidence * 100, 0).'%' : 'Pending' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-zinc-500">No public pulse mentions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <div>{{ $mentions->links() }}</div>
</div>

<script>
document.querySelector('[data-select-all]')?.addEventListener('change', function () {
    document.querySelectorAll('[data-row-check]').forEach((checkbox) => checkbox.checked = this.checked);
});
</script>
@endsection
