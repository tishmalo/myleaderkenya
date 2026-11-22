@extends('layouts.app')
@section('page_title','Audit Log')
@section('content')
<div class="mb-8"><h1 class="text-3xl font-bold text-white">Audit Log</h1><p class="mt-2 text-zinc-400">Immutable system, security, and business activity.</p></div>
<form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 md:grid-cols-5">
    <input name="actor" value="{{ request('actor') }}" placeholder="Actor name" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3">
    <input name="activity" value="{{ request('activity') }}" placeholder="Activity" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3">
    <input type="date" name="date_from" value="{{ request('date_from') }}" aria-label="From date" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3">
    <input type="date" name="date_to" value="{{ request('date_to') }}" aria-label="To date" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3">
    <button class="rounded-xl bg-emerald-600 px-5 py-3 font-bold">Filter</button>
</form>
@include('components.audit-table',['aspirantView'=>false,'auditService'=>app(\App\Services\Audit\AuditService::class)])

<section class="mt-10" aria-labelledby="candidate-contributors-title">
    <div class="mb-4">
        <h2 id="candidate-contributors-title" class="text-2xl font-bold text-white">Aspirant contributors</h2>
        <p class="mt-1 text-sm text-zinc-400">Distinct candidates recorded by “Candidate created”. Only candidates that still exist are eligible for payment.</p>
        @if(! empty($contributorFilters['date_from']) || ! empty($contributorFilters['date_to']))
            <p class="mt-2 text-sm text-emerald-400">
                Totals for {{ ! empty($contributorFilters['date_from']) ? \Illuminate\Support\Carbon::parse($contributorFilters['date_from'])->format('j M Y') : 'the beginning' }}
                &ndash;
                {{ ! empty($contributorFilters['date_to']) ? \Illuminate\Support\Carbon::parse($contributorFilters['date_to'])->format('j M Y') : 'today' }}.
            </p>
        @endif
    </div>
    <form method="GET" class="mb-4 grid gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 md:grid-cols-5">
        <input name="contributor_actor" value="{{ request('contributor_actor') }}" placeholder="Contributor name" aria-label="Contributor name" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3">
        <input type="date" name="contributor_from" value="{{ request('contributor_from') }}" aria-label="Contributor from date" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3">
        <input type="date" name="contributor_to" value="{{ request('contributor_to') }}" aria-label="Contributor to date" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3">
        <button class="rounded-xl bg-emerald-600 px-5 py-3 font-bold">Filter</button>
        <a href="{{ route('audits.index') }}" class="rounded-xl border border-zinc-700 px-5 py-3 text-center font-semibold text-zinc-300">Clear</a>
    </form>
    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/60">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-zinc-900 text-xs uppercase tracking-wider text-zinc-500">
                    <tr><th class="px-5 py-4">Actor</th><th class="px-5 py-4">Live aspirants</th><th class="px-5 py-4">Deleted / excluded</th><th class="px-5 py-4">Links added</th><th class="px-5 py-4">Payment status</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse($candidateCreatorSummary as $contributor)
                        <tr class="text-zinc-300">
                            <td class="px-5 py-4 font-semibold text-white">{{ $contributor->actor_name ?: 'User #'.$contributor->user_id }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-emerald-500/10 px-3 py-1 font-bold text-emerald-400">{{ number_format($contributor->live_candidates) }}</span></td>
                            <td class="px-5 py-4"><span class="rounded-full bg-red-500/10 px-3 py-1 font-bold text-red-400">{{ number_format($contributor->deleted_candidates) }}</span></td>
                            <td class="px-5 py-4"><span class="rounded-full bg-blue-500/10 px-3 py-1 font-bold text-blue-400">{{ number_format($contributor->links_added ?? 0) }}</span></td>
                            <td class="px-5 py-4 text-zinc-400">{{ $contributor->live_candidates > 0 ? 'Eligible count: '.number_format($contributor->live_candidates) : 'No eligible candidates' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-zinc-500">No candidate-creation activity was found for this date range.</td></tr>
                    @endforelse
                </tbody>
                @if($candidateCreatorSummary->isNotEmpty())
                    <tfoot class="border-t-2 border-zinc-700 bg-zinc-900 text-white">
                        <tr>
                            <td class="px-5 py-4 font-bold">
                                Cumulative total
                                <span class="ml-1 text-xs font-normal text-zinc-500">{{ number_format($contributorTotals['contributors']) }} contributor{{ $contributorTotals['contributors'] === 1 ? '' : 's' }}</span>
                            </td>
                            <td class="px-5 py-4"><span class="rounded-full bg-emerald-500/10 px-3 py-1 font-bold text-emerald-400">{{ number_format($contributorTotals['live']) }}</span></td>
                            <td class="px-5 py-4"><span class="rounded-full bg-red-500/10 px-3 py-1 font-bold text-red-400">{{ number_format($contributorTotals['deleted']) }}</span></td>
                            <td class="px-5 py-4"><span class="rounded-full bg-blue-500/10 px-3 py-1 font-bold text-blue-400">{{ number_format($contributorTotals['links']) }}</span></td>
                            <td class="px-5 py-4 text-zinc-400">Eligible count: {{ number_format($contributorTotals['live']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</section>
@endsection
