@extends('layouts.app')

@section('page_title', 'Send Report')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl px-6 py-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-white flex items-center gap-3">
                    <i class="fas {{ $campaign->isEmail() ? 'fa-envelope-open-text' : 'fa-comment-sms' }} text-emerald-400"></i>
                    {{ $campaign->channelLabel() }} send report
                </h1>
                <p class="text-sm text-zinc-500 mt-2">
                    {{ \App\Support\OutreachAudience::label($campaign->audience) }}
                    &middot; started {{ ($campaign->started_at ?? $campaign->created_at)->format('d M Y, H:i') }}
                    @if($campaign->creator) &middot; by {{ $campaign->creator->name }} @endif
                </p>
            </div>
            <a href="{{ route($campaign->isEmail() ? 'outreach.email' : 'outreach.sms') }}"
               class="bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-5 py-3 rounded-2xl font-medium text-zinc-300">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-zinc-950 rounded-2xl px-5 py-4">
                <p class="text-xs uppercase tracking-wide text-zinc-500">Recipients</p>
                <p class="text-2xl font-semibold text-white mt-1">{{ number_format($campaign->total_recipients) }}</p>
            </div>
            <div class="bg-zinc-950 rounded-2xl px-5 py-4">
                <p class="text-xs uppercase tracking-wide text-zinc-500">Sent</p>
                <p class="text-2xl font-semibold text-emerald-400 mt-1">{{ number_format($campaign->sent_count) }}</p>
            </div>
            <div class="bg-zinc-950 rounded-2xl px-5 py-4">
                <p class="text-xs uppercase tracking-wide text-zinc-500">Failed</p>
                <p class="text-2xl font-semibold text-red-400 mt-1">{{ number_format($campaign->failed_count) }}</p>
            </div>
            <div class="bg-zinc-950 rounded-2xl px-5 py-4">
                <p class="text-xs uppercase tracking-wide text-zinc-500">Status</p>
                <p class="text-lg font-semibold text-white mt-1 capitalize">{{ $campaign->status }}</p>
            </div>
        </div>

        <div class="mt-5 rounded-2xl border border-zinc-800 bg-zinc-950 p-4">
            @if($campaign->subject)
                <p class="text-sm font-semibold text-white">{{ $campaign->subject }}</p>
            @endif
            <p class="text-sm text-zinc-400 mt-1 whitespace-pre-line">{{ $campaign->body }}</p>
        </div>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="px-5 py-4 border-b border-zinc-800 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-zinc-100">Who received it</h2>
            <form method="GET" action="{{ route('outreach.show', $campaign) }}" class="flex items-center gap-2">
                <select name="status" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-2 text-sm text-white">
                    <option value="">All statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-2xl px-4 py-2 text-sm text-zinc-200">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[860px] w-full text-sm">
                <thead class="bg-zinc-950 text-xs uppercase tracking-wide text-zinc-500">
                    <tr>
                        <th class="px-5 py-4 text-left">Name</th>
                        <th class="px-5 py-4 text-left">{{ $campaign->isEmail() ? 'Email' : 'Phone' }}</th>
                        <th class="px-5 py-4 text-left">Status</th>
                        <th class="px-5 py-4 text-left">Sent at</th>
                        <th class="px-5 py-4 text-left">Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse($recipients as $recipient)
                        <tr class="text-zinc-300">
                            <td class="px-5 py-3 font-medium text-white">{{ $recipient->name ?: '—' }}</td>
                            <td class="px-5 py-3">{{ $campaign->isEmail() ? ($recipient->email ?: '—') : ($recipient->phone ?: '—') }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold
                                    @class([
                                        'bg-emerald-500/10 text-emerald-400' => $recipient->status === 'sent',
                                        'bg-red-500/10 text-red-400' => $recipient->status === 'failed',
                                        'bg-zinc-800 text-zinc-300' => ! in_array($recipient->status, ['sent', 'failed'], true),
                                    ])">{{ ucfirst($recipient->status) }}</span>
                            </td>
                            <td class="px-5 py-3 text-zinc-400">{{ $recipient->sent_at?->format('d M Y, H:i') ?? '—' }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $recipient->error ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-zinc-500">No recipients recorded for this filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recipients->hasPages())
            <div class="px-5 py-4 border-t border-zinc-800">{{ $recipients->links() }}</div>
        @endif
    </div>
</div>
@endsection
