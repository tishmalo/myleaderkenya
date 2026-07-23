@extends('layouts.app')

@section('page_title', 'Campaign Tool Requests')

@section('content')
<div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3"><i class="fas fa-lightbulb text-emerald-500"></i> Campaign Tool Requests</h1>
        <a href="{{ route('campaign-tools.index') }}" class="bg-zinc-800 hover:bg-zinc-700 px-5 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-bullhorn mr-2"></i> Campaign Tools</a>
    </div>

    <form method="GET" action="{{ route('campaign-tool-requests.index') }}" class="mb-6 grid md:grid-cols-[180px_240px_1fr_auto] gap-3">
        <select name="status" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
            <option value="">All Statuses</option>
            @foreach(\App\Models\CampaignToolRequest::STATUSES as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
            @endforeach
        </select>
        <select name="campaign_tool_id" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
            <option value="">All Tools</option>
            @foreach($campaignTools as $tool)
                <option value="{{ $tool->id }}" {{ (string) request('campaign_tool_id') === (string) $tool->id ? 'selected' : '' }}>{{ $tool->title }}</option>
            @endforeach
        </select>
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search requester, contact, feature, or use case" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
        <button class="bg-zinc-700 hover:bg-zinc-600 px-6 py-3 rounded-2xl text-sm font-medium">Filter</button>
    </form>

    <div class="grid gap-4">
        @forelse($requests as $requestItem)
            <article class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
                <div class="grid lg:grid-cols-[1fr_320px] gap-6">
                    <div>
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <h2 class="text-2xl font-semibold text-white">{{ $requestItem->requested_feature }}</h2>
                                <p class="text-zinc-400">{{ $requestItem->campaignTool->title ?? 'Campaign Tool' }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300">{{ str_replace('_', ' ', ucfirst($requestItem->status)) }}</span>
                        </div>
                        <div class="grid md:grid-cols-2 gap-3 text-sm text-zinc-300">
                            <div><span class="text-zinc-500">Requester:</span> {{ $requestItem->requester_name }}</div>
                            <div><span class="text-zinc-500">Submitted:</span> {{ $requestItem->created_at->format('M j, Y H:i') }}</div>
                            <div><span class="text-zinc-500">Phone:</span> {{ $requestItem->phone ?: '-' }}</div>
                            <div><span class="text-zinc-500">Email:</span> {{ $requestItem->email ?: '-' }}</div>
                            <div><span class="text-zinc-500">User:</span> {{ $requestItem->user->name ?? $requestItem->user->username ?? '-' }}</div>
                            <div><span class="text-zinc-500">Candidate:</span> {{ $requestItem->candidate->name ?? '-' }}</div>
                        </div>
                        @if($requestItem->use_case)
                            <p class="mt-4 text-zinc-300 leading-relaxed">{{ $requestItem->use_case }}</p>
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