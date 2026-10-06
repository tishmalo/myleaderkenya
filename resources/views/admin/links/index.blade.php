@extends('layouts.app')

@section('page_title', 'Pages & Links')

@section('content')
<div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3">
            <i class="fas fa-link text-emerald-500"></i> Pages &amp; Links
            <span class="text-sm font-normal text-zinc-500">User-submitted community pages, groups and links</span>
        </h1>
        <a href="{{ route('links.create') }}"
           class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i> Add Link/Page
        </a>
    </div>

    <form method="GET" action="{{ route('links.index') }}" class="mb-6 flex flex-wrap gap-3">
        <select name="status" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
            <option value="">All statuses</option>
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" {{ $activeStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button class="bg-zinc-700 hover:bg-zinc-600 px-6 py-3 rounded-2xl text-sm font-medium">Filter</button>
        @if($activeStatus)
            <a href="{{ route('links.index') }}" class="bg-zinc-800 hover:bg-zinc-700 px-5 py-3 rounded-2xl text-sm font-medium">Reset</a>
        @endif
    </form>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr>
                    <th class="px-6 py-4 text-left">Link</th>
                    <th class="px-6 py-4 text-left">Targets</th>
                    <th class="px-6 py-4 text-left">Submitted by</th>
                    <th class="px-6 py-4 text-center">Approval</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 text-zinc-300">
                @forelse($links as $link)
                    <tr class="hover:bg-zinc-800/70">
                        <td class="px-6 py-4">
                            <div class="flex items-start gap-3">
                                <i class="{{ $link->platform_icon }} mt-1 text-emerald-400"></i>
                                <div>
                                    <p class="font-medium text-white">{{ $link->display_title }}</p>
                                    <p class="text-xs text-zinc-500 mt-0.5">{{ $link->platform_label }}</p>
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener nofollow" class="text-xs text-blue-400 hover:underline break-all">{{ $link->url }}</a>
                                    @if($link->comment)
                                        <p class="text-xs text-zinc-500 mt-1">{{ $link->comment }}</p>
                                    @endif
                                    @if($link->followers)
                                        <p class="text-xs text-zinc-500 mt-1"><i class="fas fa-users mr-1"></i>{{ number_format($link->followers) }} followers</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-zinc-400">
                            <div class="flex flex-wrap gap-1">
                                @foreach(collect([$link->candidate?->name, $link->politicalParty?->name, $link->ward?->name, $link->constituency?->name, $link->county?->name])->filter() as $target)
                                    <span class="px-2 py-1 rounded-full text-xs bg-zinc-800 text-zinc-300">{{ $target }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-zinc-400">
                            <p>{{ $link->creator?->name ?? 'Deleted user' }}</p>
                            <p class="text-xs text-zinc-600 mt-1">{{ $link->created_at->format('M j, Y H:i') }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($link->approval_status === 'approved')
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-emerald-500/20 text-emerald-400">Approved</span>
                            @elseif($link->approval_status === 'rejected')
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-500/20 text-red-400">Rejected</span>
                            @else
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-amber-500/20 text-amber-400">Pending</span>
                            @endif
                            @if($link->reviewed_at)
                                <p class="text-[11px] text-zinc-600 mt-1">{{ $link->reviewed_at->format('M j, Y') }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <a href="{{ route('links.edit', $link) }}" class="text-blue-400 hover:text-blue-500 mx-2" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            @if($link->approval_status !== 'approved')
                                <form action="{{ route('links.approval', $link) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="text-emerald-400 hover:text-emerald-500 mx-2" title="Approve &amp; publish">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                </form>
                            @endif
                            @if($link->approval_status !== 'rejected')
                                <form action="{{ route('links.approval', $link) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="text-amber-400 hover:text-amber-500 mx-2" title="Reject">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                            @endif
                            <button onclick="deleteResourceLink('{{ route('links.destroy', $link) }}')" class="text-red-400 hover:text-red-500 mx-2" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-16 text-zinc-500">No links have been submitted yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-center">
        {{ $links->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteResourceLink(url) {
    showDeleteModal(url, 'Delete this <strong>link</strong>? It will be removed from every page.');
}
</script>
@endpush