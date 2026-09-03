@extends('layouts.app')

@section('page_title', 'Spam Filter Hub')

@section('content')
<div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="mb-6 rounded-2xl border border-amber-500/30 bg-amber-500/10 px-5 py-4 text-amber-200">{{ session('warning') }}</div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3"><i class="fas fa-shield-halved text-emerald-500"></i> Spam Filter Hub</h1>
        <a href="{{ route('campaign-tool-requests.index') }}" class="bg-zinc-800 hover:bg-zinc-700 px-5 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-inbox mr-2"></i> Campaign Requests</a>
    </div>

    <div class="grid md:grid-cols-4 gap-4 mb-8">
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Quarantined requests</p>
            <p class="mt-2 text-3xl font-semibold text-amber-300">{{ $spamRequestCount }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Recorded samples</p>
            <p class="mt-2 text-3xl font-semibold text-red-300">{{ $rejectedCount }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Active rules</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-300">{{ $rules->flatten()->where('enabled', true)->count() }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Blocked IPs</p>
            <p class="mt-2 text-3xl font-semibold text-sky-300">{{ $blockedIps->count() }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-8">
        <section class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <h2 class="text-lg font-semibold text-white mb-1 flex items-center gap-2"><i class="fas fa-paste text-emerald-500"></i> Paste a spam message</h2>
            <p class="text-sm text-zinc-500 mb-4">Paste spam you have seen (from emails, logs, or requests that slipped through). We will extract suggested patterns.</p>
            <form method="POST" action="{{ route('spam-filter.analyze') }}" class="grid gap-3">
                @csrf
                <textarea name="text" rows="6" maxlength="20000" placeholder="Paste the full spam message here..." class="bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white placeholder:text-zinc-500" required></textarea>
                <button class="bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-wand-magic-sparkles mr-2"></i> Analyze &amp; extract patterns</button>
            </form>
        </section>

        <section class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <h2 class="text-lg font-semibold text-white mb-1 flex items-center gap-2"><i class="fas fa-plus-circle text-emerald-500"></i> Add a rule manually</h2>
            <p class="text-sm text-zinc-500 mb-4">Add a keyword, domain, email pattern, phone pattern, IP or regex directly.</p>
            <form method="POST" action="{{ route('spam-filter.rules.store') }}" class="grid md:grid-cols-2 gap-3">
                @csrf
                <select name="type" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    @foreach(\App\Models\SpamRule::TYPES as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
                <input type="text" name="value" maxlength="500" placeholder="e.g. quick cash loans" required class="bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white placeholder:text-zinc-500">
                <button class="md:col-span-2 bg-zinc-700 hover:bg-zinc-600 px-5 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-plus mr-2"></i> Add rule</button>
            </form>

            @php($suggestions = session('spam_suggestions'))
            @if($suggestions && ! empty(array_filter($suggestions)))
                <div class="mt-6 rounded-2xl border border-amber-500/25 bg-amber-500/10 p-4">
                    <h3 class="text-sm font-semibold text-amber-300 mb-1 flex items-center gap-2"><i class="fas fa-lightbulb"></i> Suggested patterns</h3>
                    <p class="text-xs text-zinc-400 mb-3">From: <em class="break-all">{{ \Illuminate\Support\Str::limit(session('spam_suggestion_text'), 160) }}</em></p>
                    <div class="grid gap-3">
                        @foreach($suggestions as $type => $values)
                            @if(empty($values))
                                @continue
                            @endif
                            <div>
                                <p class="text-xs uppercase tracking-wider text-zinc-500 mb-2">{{ $type }} ({{ count($values) }})</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($values as $value)
                                        <form method="POST" action="{{ route('spam-filter.rules.store') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="{{ $type }}">
                                            <input type="hidden" name="value" value="{{ $value }}">
                                            <button class="rounded-full border border-zinc-700 bg-zinc-800 px-3 py-1 text-xs text-zinc-300 hover:border-emerald-600 hover:text-emerald-300">
                                                + {{ $value }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-8">
        <section class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2"><i class="fas fa-list-check text-emerald-500"></i> Active rules</h2>
            @if($rules->isEmpty())
                <p class="text-sm text-zinc-500">No database rules yet. Add patterns or paste spam to build the list.</p>
            @else
                <div class="space-y-3">
                    @foreach($rules as $type => $typeRules)
                        <div>
                            <p class="text-xs uppercase tracking-wider text-zinc-500 mb-2">{{ $type }}</p>
                            <div class="space-y-2">
                                @foreach($typeRules as $rule)
                                    <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5">
                                        <span class="text-sm text-zinc-200 break-all">{{ $rule->value }}</span>
                                        <div class="flex shrink-0 items-center gap-2">
                                            <span class="text-xs {{ $rule->enabled ? 'text-emerald-400' : 'text-zinc-500' }}">{{ $rule->enabled ? 'on' : 'off' }}</span>
                                            <form method="POST" action="{{ route('spam-filter.rules.toggle', $rule) }}">
                                                @csrf @method('PATCH')
                                                <button class="text-sm text-zinc-400 hover:text-white" title="{{ $rule->enabled ? 'Disable' : 'Enable' }}">
                                                    <i class="fas {{ $rule->enabled ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('spam-filter.rules.destroy', $rule) }}" onsubmit="return confirm('Delete this rule?')">
                                                @csrf @method('DELETE')
                                                <button class="text-sm text-red-400 hover:text-red-300" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2"><i class="fas fa-ban text-emerald-500"></i> Blocked IPs &amp; overrides</h2>

            <div class="flex flex-wrap gap-2 mb-6">
                @forelse($blockedIps as $ip)
                    <span class="rounded-full border border-red-500/40 bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-300">{{ $ip }}</span>
                @empty
                    <span class="text-sm text-zinc-500">No blocked IPs.</span>
                @endforelse
            </div>

            @if($overrides->isNotEmpty())
                <p class="text-xs uppercase tracking-wider text-zinc-500 mb-2">Allow overrides (reCAPTCHA-cleared)</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($overrides as $override)
                        <span class="rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300">
                            {{ $override->ip }} {{ $override->expires_at ? '· until '.$override->expires_at->format('M j, H:i') : '· permanent' }}
                        </span>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2"><i class="fas fa-shield text-emerald-500"></i> Recorded spam samples</h2>
        <div class="space-y-3">
            @forelse($samples as $sample)
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-semibold text-red-300">{{ $sample->reason ?: 'suspected' }}</span>
                        <span class="text-xs text-zinc-500">{{ $sample->created_at->diffForHumans() }}</span>
                        @if($sample->ip)
                            <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs text-zinc-400">{{ $sample->ip }}</span>
                        @endif
                        @if($sample->campaignToolRequest)
                            <span class="text-xs text-zinc-500">→ request #{{ $sample->campaign_tool_request_id }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-zinc-300 break-words whitespace-pre-wrap">{{ \Illuminate\Support\Str::limit(implode(' ', array_filter(array_intersect_key($sample->payload ?? [], array_flip(['requester_name', 'requested_feature', 'use_case'])))), 600) }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @if($sample->ip)
                            <form method="POST" action="{{ route('spam-filter.samples.block-ip', $sample) }}">
                                @csrf
                                <button class="rounded-xl border border-red-500/40 px-4 py-2 text-sm text-red-300 hover:bg-red-500/10"><i class="fas fa-ban mr-1"></i> Block IP</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('spam-filter.samples.destroy', $sample) }}" onsubmit="return confirm('Delete this sample?')">
                            @csrf @method('DELETE')
                            <button class="rounded-xl border border-zinc-700 px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800"><i class="fas fa-trash mr-1"></i> Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-zinc-500">No spam samples recorded yet.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $samples->links() }}</div>
    </section>
</div>
@endsection