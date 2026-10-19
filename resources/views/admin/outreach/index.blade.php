@extends('layouts.app')

@section('page_title', 'Send '.$channelLabel)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-300">{{ session('error') }}</div>
    @endif

    @if($channel === 'sms' && ! $smsConfigured)
        <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 px-5 py-4 text-amber-200">
            <i class="fas fa-triangle-exclamation mr-2"></i>
            The platform SMS account is not configured yet, so sends will fail and each recipient is marked with the reason.
            Set <code>SMS_BASE_URL</code>, <code>SMS_USERNAME</code>, <code>SMS_PASSWORD</code> and <code>SMS_SENDER</code> in the environment.
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-zinc-900 border border-zinc-800 rounded-3xl p-6 space-y-5">
            <div>
                <h1 class="text-2xl font-semibold text-white flex items-center gap-3">
                    <i class="fas {{ $channel === 'email' ? 'fa-envelope-open-text' : 'fa-comment-sms' }} text-emerald-400"></i>
                    Send {{ $channelLabel }}
                </h1>
                <p class="text-sm text-zinc-500 mt-2">
                    Pick an audience, write the message with placeholders, and every recipient is recorded so you can see
                    who received it.
                </p>
            </div>

            @if($canSend)
                <form action="{{ route('outreach.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="channel" value="{{ $channel }}">

                    <div>
                        <label for="audience" class="block text-sm text-zinc-400 mb-2">Send to <span class="text-red-500">*</span></label>
                        <select id="audience" name="audience" required
                                class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            <option value="">Choose an audience</option>
                            @foreach($audiences as $key => [$label, $description])
                                <option value="{{ $key }}" @selected(old('audience') === $key)>
                                    {{ $label }} — {{ number_format($audienceCounts[$key] ?? 0) }} {{ $channel === 'email' ? 'with an email' : 'with a phone' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-zinc-500 mt-2">
                            @foreach($audiences as $key => [$label, $description])
                                <span class="block"><strong class="text-zinc-400">{{ $label }}:</strong> {{ $description }}</span>
                            @endforeach
                        </p>
                        @error('audience')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>

                    @if($channel === 'email')
                        <div>
                            <label for="subject" class="block text-sm text-zinc-400 mb-2">Subject <span class="text-red-500">*</span></label>
                            <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required maxlength="150"
                                   placeholder="Your 2027 aspirant profile is live"
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            @error('subject')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <div>
                        <label for="body" class="block text-sm text-zinc-400 mb-2">Message <span class="text-red-500">*</span></label>
                        <textarea id="body" name="body" rows="8" required maxlength="2000" data-outreach-body
                                  placeholder="Hi {name}, your {position} profile for {county} is live. Open it here: {aspirant_profile_link}"
                                  class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500 font-mono text-sm">{{ old('body') }}</textarea>
                        @error('body')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                        <p class="text-xs text-zinc-500 mt-2">
                            <span data-outreach-count>0</span> / 2000 characters
                            @if($channel === 'sms')
                                &middot; <span data-outreach-segments>1</span> SMS segment(s)
                            @endif
                        </p>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4">
                        <p class="text-sm font-semibold text-zinc-300 mb-2">Placeholders you can use</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($variables as $token => $description)
                                <button type="button" data-outreach-token="{{ '{'.$token.'}' }}" title="{{ $description }}"
                                        class="rounded-full bg-zinc-800 px-3 py-1 text-xs font-mono text-zinc-300 hover:bg-zinc-700">{{ '{'.$token.'}' }}</button>
                            @endforeach
                        </div>
                        <p class="text-xs text-zinc-500 mt-3">
                            Each recipient gets their own values. For example
                            <span data-outreach-preview class="text-zinc-300">{{ $previewSample['name'] }} · {{ $previewSample['position'] }} · {{ $previewSample['county'] }}</span>.
                        </p>
                    </div>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl font-semibold">
                        <i class="fas fa-paper-plane mr-2"></i> Send {{ $channelLabel }}
                    </button>
                </form>
            @else
                <p class="text-sm text-zinc-500">Your role can read these screens but not send messages.</p>
            @endif
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
            <div class="bg-zinc-950 px-5 py-4">
                <h2 class="text-lg font-semibold text-zinc-100"><i class="fas fa-clock-rotate-left text-emerald-400 mr-2"></i>Recent {{ $channelLabel }} sends</h2>
            </div>

            <div class="divide-y divide-zinc-800">
                @forelse($campaigns as $campaign)
                    <a href="{{ route('outreach.show', $campaign) }}" class="block px-5 py-4 hover:bg-zinc-800/70">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium text-white truncate">{{ $campaign->subject ?: \Illuminate\Support\Str::limit($campaign->body, 40) }}</span>
                            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold
                                @class([
                                    'bg-emerald-500/10 text-emerald-400' => $campaign->status === 'sent',
                                    'bg-red-500/10 text-red-400' => $campaign->status === 'failed',
                                    'bg-zinc-800 text-zinc-300' => ! in_array($campaign->status, ['sent', 'failed'], true),
                                ])">{{ ucfirst($campaign->status) }}</span>
                        </div>
                        <p class="text-xs text-zinc-500 mt-1">
                            {{ \App\Support\OutreachAudience::label($campaign->audience) }} &middot;
                            {{ number_format($campaign->sent_count) }}/{{ number_format($campaign->total_recipients) }} sent
                            @if($campaign->failed_count > 0) &middot; {{ number_format($campaign->failed_count) }} failed @endif
                            &middot; {{ $campaign->created_at->diffForHumans() }}
                        </p>
                    </a>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-zinc-500">Nothing sent yet.</p>
                @endforelse
            </div>

            @if($campaigns->hasPages())
                <div class="px-5 py-4 border-t border-zinc-800">{{ $campaigns->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var body = document.querySelector('[data-outreach-body]');
    if (! body) return;

    var counter = document.querySelector('[data-outreach-count]');
    var segments = document.querySelector('[data-outreach-segments]');

    function update() {
        if (counter) counter.textContent = body.value.length;
        if (segments) segments.textContent = Math.max(1, Math.ceil(body.value.length / 160));
    }

    body.addEventListener('input', update);
    update();

    document.querySelectorAll('[data-outreach-token]').forEach(function (button) {
        button.addEventListener('click', function () {
            var token = button.getAttribute('data-outreach-token');
            var start = body.selectionStart || body.value.length;
            var end = body.selectionEnd || body.value.length;
            body.value = body.value.slice(0, start) + token + body.value.slice(end);
            body.focus();
            body.selectionStart = body.selectionEnd = start + token.length;
            update();
        });
    });
})();
</script>
@endpush
