@extends('layouts.app')

@section('page_title', 'Poll Comments')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-comments text-emerald-500"></i>
            Poll Comments
        </h1>
        <a href="{{ route('polls.index') }}"
           class="bg-zinc-800 hover:bg-zinc-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <i class="fas fa-chart-simple"></i> All Polls
        </a>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach($statusTabs as $tab)
            <a href="{{ $tab['url'] }}"
               class="px-5 py-2 rounded-2xl text-sm font-medium {{ $tab['active'] ? 'bg-emerald-600 text-white' : 'bg-zinc-800 hover:bg-zinc-700 text-zinc-300' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('poll-comments.index') }}" class="mb-6 flex gap-2 max-w-lg">
        @if($currentStatus)
            <input type="hidden" name="status" value="{{ $currentStatus }}">
        @endif
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Search comments, users or polls…"
               class="flex-1 bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-2.5 text-sm text-white focus:border-emerald-600 focus:outline-none">
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 px-5 py-2.5 rounded-2xl text-sm font-medium">
            <i class="fas fa-search"></i>
        </button>
    </form>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr>
                    <th class="px-6 py-4 text-left">Comment</th>
                    <th class="px-6 py-4 text-left">Poll</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-left">Submitted</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($rows as $row)
                <tr class="hover:bg-zinc-800/70" id="poll-comment-{{ $row['id'] }}">
                    <td class="px-6 py-4">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center font-semibold shrink-0">
                                {{ $row['initial'] }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-white">{{ $row['author'] }}</p>
                                <p class="text-sm text-zinc-400 mt-1 whitespace-pre-line break-words">{{ $row['excerpt'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($row['poll_removed'])
                            <span class="text-sm text-zinc-600">Poll removed</span>
                        @else
                            <a href="{{ $row['poll_edit_url'] }}" class="text-sm text-blue-400 hover:text-blue-500">
                                {{ $row['poll_question'] }}
                            </a>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $row['status_badge_class'] }}">
                            {{ $row['status_label'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-500">
                        {{ $row['submitted_at'] }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-3">
                            @if($row['can_approve'])
                                <button onclick="setPollCommentStatus({{ $row['id'] }}, 'approved')"
                                        title="Approve" class="text-emerald-400 hover:text-emerald-500">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                            @if($row['can_reject'])
                                <button onclick="setPollCommentStatus({{ $row['id'] }}, 'rejected')"
                                        title="Reject" class="text-orange-400 hover:text-orange-500">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @endif
                            @if($row['can_reopen'])
                                <button onclick="setPollCommentStatus({{ $row['id'] }}, 'pending')"
                                        title="Move back to pending" class="text-zinc-400 hover:text-zinc-300">
                                    <i class="fas fa-clock"></i>
                                </button>
                            @endif
                            <button onclick="deletePollComment({{ $row['id'] }})"
                                    title="Delete" class="text-red-400 hover:text-red-500">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-16 text-zinc-500">No comments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-center">
        {{ $paginator->appends($queryString)->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

async function setPollCommentStatus(id, status) {
    const response = await fetch(`/poll-comments/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({ status }),
    });

    if (response.ok) {
        window.location.reload();
    } else {
        alert('Could not update the comment.');
    }
}

function deletePollComment(id) {
    showDeleteModal(`/poll-comments/${id}`, 'Delete this comment permanently?');
}
</script>
@endpush
