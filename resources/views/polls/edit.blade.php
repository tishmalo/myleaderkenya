@extends('layouts.app')

@section('page_title', 'Edit Poll')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white">Edit Poll</h1>
        <a href="{{ route('polls.index') }}" class="text-zinc-400 hover:text-white">← Back to Polls</a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 mb-8" data-poll-results
         data-results-url="{{ route('polls.results', $poll) }}">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-semibold text-white flex items-center gap-3">
                <i class="fas fa-chart-bar text-emerald-500"></i> Results
            </h2>
            <div class="flex items-center gap-4">
                <span class="text-sm text-zinc-400">
                    <strong class="text-white" data-total-votes>{{ number_format($results->sum('votes')) }}</strong>
                    {{ \Illuminate\Support\Str::plural('vote', $results->sum('votes')) }}
                </span>
                <button type="button" data-refresh-results
                        class="bg-zinc-800 hover:bg-zinc-700 px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-rotate"></i> Refresh
                </button>
            </div>
        </div>

        @if($results->isEmpty())
            <p class="text-zinc-500 text-sm">This poll has no options yet.</p>
        @else
            <div class="space-y-4">
                @foreach($results as $row)
                    <div data-result-row="{{ $row['option']->id }}">
                        <div class="flex items-center justify-between mb-1.5 text-sm">
                            <span class="text-white">{{ $row['option']->label }}</span>
                            <span class="text-zinc-400">
                                <strong class="text-emerald-400" data-row-percent>{{ $row['percent'] }}%</strong>
                                · <span data-row-votes>{{ number_format($row['votes']) }}</span>
                            </span>
                        </div>
                        <div class="h-2.5 rounded-full bg-zinc-950 overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                 data-row-bar style="width: {{ $row['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <p class="mt-5 text-xs text-zinc-500">
            <i class="fas fa-circle-info"></i>
            These numbers are always visible to you, whether or not the public can see the results.
        </p>
    </div>

    @include('polls._form', ['poll' => $poll])
</div>
@endsection

@push('scripts')
<script>
(function () {
    var panel = document.querySelector('[data-poll-results]');
    if (!panel) return;

    var button = panel.querySelector('[data-refresh-results]');
    var icon = button.querySelector('i');

    button.addEventListener('click', function () {
        icon.classList.add('fa-spin');

        fetch(panel.dataset.resultsUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                panel.querySelector('[data-total-votes]').textContent = Number(data.total).toLocaleString();

                (data.results || []).forEach(function (row) {
                    var line = panel.querySelector('[data-result-row="' + row.option_id + '"]');
                    if (!line) return;
                    line.querySelector('[data-row-percent]').textContent = row.percent + '%';
                    line.querySelector('[data-row-votes]').textContent = Number(row.votes).toLocaleString();
                    line.querySelector('[data-row-bar]').style.width = row.percent + '%';
                });
            })
            .finally(function () { icon.classList.remove('fa-spin'); });
    });
})();
</script>
@endpush
