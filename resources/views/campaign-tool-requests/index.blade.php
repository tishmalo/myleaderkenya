@extends('layouts.app')

@section('page_title', 'Campaign Tool Requests')

@section('content')
<div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="mb-6 rounded-2xl border border-amber-500/30 bg-amber-500/10 px-5 py-4 text-amber-200">{{ session('warning') }}</div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3"><i class="fas fa-lightbulb text-emerald-500"></i> Campaign Tool Requests</h1>
        <a href="{{ route('campaign-tools.index') }}" class="bg-zinc-800 hover:bg-zinc-700 px-5 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-bullhorn mr-2"></i> Campaign Tools</a>
    </div>

    <form method="GET" action="{{ route('campaign-tool-requests.index') }}" class="mb-6 grid md:grid-cols-[170px_180px_180px_220px_1fr_auto] gap-3">
        <select name="status" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
            <option value="">All Statuses</option>
            @foreach(\App\Models\CampaignToolRequest::STATUSES as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
            @endforeach
        </select>
        <select name="request_type" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
            <option value="">All Requests</option>
            <option value="feature" {{ request('request_type') === 'feature' ? 'selected' : '' }}>Feature Requests</option>
            <option value="activation" {{ request('request_type') === 'activation' ? 'selected' : '' }}>Activation Requests</option>
            <option value="adoption" {{ request('request_type') === 'adoption' ? 'selected' : '' }}>Adoption Sponsorships</option>
        </select>
        <select name="payment_status" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
            <option value="">All Payments</option>
            @foreach(['awaiting_payment', 'paid', 'refunded', 'not_required'] as $paymentStatus)
                <option value="{{ $paymentStatus }}" {{ request('payment_status') === $paymentStatus ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($paymentStatus)) }}</option>
            @endforeach
        </select>
        <select name="campaign_tool_id" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
            <option value="">All Tools</option>
            @foreach($campaignTools as $tool)
                <option value="{{ $tool->id }}" {{ (string) request('campaign_tool_id') === (string) $tool->id ? 'selected' : '' }}>{{ $tool->title }}</option>
            @endforeach
        </select>
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search requester, contact, tool, feature, or notes" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
        <button class="bg-zinc-700 hover:bg-zinc-600 px-6 py-3 rounded-2xl text-sm font-medium">Filter</button>
    </form>

    <div class="grid gap-4">
        @forelse($requests as $requestItem)
            @php
                $type = $requestItem->request_type ?: 'feature';
                $isActivation = $type === 'activation';
                $isAdoption = $type === 'adoption';
                $toolTitle = $requestItem->tool_title ?: ($requestItem->campaignTool->title ?? 'Campaign Tool');
            @endphp
            <article class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
                <div class="grid lg:grid-cols-[1fr_320px] gap-6">
                    <div>
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $isAdoption ? 'bg-emerald-500/15 text-emerald-300' : ($isActivation ? 'bg-amber-500/15 text-amber-300' : 'bg-blue-500/15 text-blue-300') }}">
                                        {{ $isAdoption ? 'Adoption Sponsorship' : ($isActivation ? 'Activation Request' : 'Feature Request') }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300">{{ str_replace('_', ' ', ucfirst($requestItem->status)) }}</span>
                                </div>
                                <h2 class="text-2xl font-semibold text-white">{{ ($isActivation || $isAdoption) ? $toolTitle : $requestItem->requested_feature }}</h2>
                                <p class="text-zinc-400">{{ $requestItem->campaignTool->title ?? $toolTitle }}</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-3 text-sm text-zinc-300">
                            <div><span class="text-zinc-500">Requester:</span> {{ $requestItem->requester_name }}</div>
                            <div><span class="text-zinc-500">Submitted:</span> {{ $requestItem->created_at->format('M j, Y H:i') }}</div>
                            <div><span class="text-zinc-500">Phone:</span> {{ $requestItem->phone ?: '-' }}</div>
                            <div><span class="text-zinc-500">Email:</span> {{ $requestItem->email ?: '-' }}</div>
                            <div><span class="text-zinc-500">User:</span> {{ $requestItem->user->name ?? $requestItem->user->username ?? '-' }}</div>
                            <div><span class="text-zinc-500">Candidate:</span> {{ $requestItem->candidate->name ?? '-' }}</div>
                            @if($isActivation)
                                <div><span class="text-zinc-500">Position:</span> {{ $requestItem->candidate->position->name ?? '-' }}</div>
                                <div><span class="text-zinc-500">Tool key:</span> {{ $requestItem->tool_key ?: '-' }}</div>
                            @endif                            @if($isAdoption)
                                <div><span class="text-zinc-500">Tokens:</span> {{ number_format($requestItem->tokens_required) }}</div>
                                <div><span class="text-zinc-500">Payment:</span> <strong class="{{ $requestItem->payment_status === 'paid' ? 'text-emerald-300' : ($requestItem->payment_status === 'refunded' ? 'text-blue-300' : 'text-amber-300') }}">{{ str_replace('_', ' ', ucfirst($requestItem->payment_status)) }}</strong></div>
                                <div class="md:col-span-2"><span class="text-zinc-500">Wallet transaction:</span> {{ $requestItem->user_token_transaction_id ?: '-' }}</div>
                            @endif
                        </div>
                        @if(! $isActivation && $requestItem->selectedTools->isNotEmpty())
                            <div class="mt-4">
                                <div class="mb-2 text-sm text-zinc-500">{{ $isAdoption ? 'Sponsored campaign tools:' : 'Other services requested:' }}</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($requestItem->selectedTools as $selectedTool)
                                        <span class="rounded-full border border-emerald-500/25 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300">{{ $selectedTool->title }}@if($isAdoption) · {{ number_format($selectedTool->sponsorship_token_cost) }} tokens @endif</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($requestItem->disabled_reason)
                            <div class="mt-4 rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm text-amber-100">
                                <span class="text-amber-300">Setup reason:</span> {{ $requestItem->disabled_reason }}
                            </div>
                        @endif
                        @if($requestItem->use_case)
                            <p class="mt-4 text-zinc-300 leading-relaxed">{{ $requestItem->use_case }}</p>
                        @endif
                        @if($isActivation)
                            <div class="mt-5 flex flex-wrap gap-3">
                                @if($requestItem->candidate)
                                    <a href="{{ route('candidates.edit', $requestItem->candidate) }}#tools" class="bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-id-badge mr-2"></i>Open Candidate</a>
                                @endif
                                <a href="{{ route('campaign-tools.index') }}" class="bg-zinc-800 hover:bg-zinc-700 px-5 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-bullhorn mr-2"></i>Campaign Tools</a>
                            </div>
                        @endif
                    </div>
                    <div class="grid gap-3">
                        @if(auth()->user()?->canAccess('campaign-tool-requests.update'))
                            <form method="POST" action="{{ route('campaign-tool-requests.update', $requestItem) }}" class="grid gap-3">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                                    @foreach(\App\Models\CampaignToolRequest::STATUSES as $status)
                                        <option value="{{ $status }}" {{ $requestItem->status === $status ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                                    @endforeach
                                </select>
                                <textarea name="admin_notes" rows="4" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white" placeholder="Admin notes">{{ $requestItem->admin_notes }}</textarea>
                                <button class="bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl text-sm font-medium">Update Request</button>
                            </form>
                        @elseif($requestItem->admin_notes)
                            <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-sm text-zinc-300">{{ $requestItem->admin_notes }}</div>
                        @endif

                        @if(auth()->user()?->canAccess('campaign-tool-requests.delete'))
                            <form method="POST" action="{{ route('campaign-tool-requests.destroy', $requestItem) }}" onsubmit="return confirm('Delete this campaign tool request?')">
                                @csrf
                                @method('DELETE')
                                <button class="w-full border border-red-500/40 text-red-300 hover:bg-red-500/10 px-5 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-trash mr-2"></i> Delete Request</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-12 text-center text-zinc-500">No campaign tool requests found.</div>
        @endforelse
    </div>

    <div class="mt-8">{{ $requests->appends(request()->query())->links() }}</div>
</div>
@endsection

