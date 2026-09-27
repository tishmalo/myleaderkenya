@extends('layouts.app')

@section('page_title', 'Article Comments')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-comments text-emerald-500"></i>
            Article Comments
        </h1>
        <a href="{{ route('news.index') }}"
           class="bg-zinc-800 hover:bg-zinc-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <i class="fas fa-newspaper"></i> All Articles
        </a>
    </div>

    <!-- Status filter -->
    <div class="mb-6 flex flex-wrap gap-2">
        @foreach(['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
            <a href="{{ route('news-comments.index', $value ? ['status' => $value] : []) }}"
               class="px-5 py-2 rounded-2xl text-sm font-medium {{ request('status') === $value || (request('status') === null && $value === '') ? 'bg-emerald-600 text-white' : 'bg-zinc-800 hover:bg-zinc-700 text-zinc-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('news-comments.index') }}" class="mb-6 flex gap-2 max-w-lg">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search comments, users or articles…"
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
                    <th class="px-6 py-4 text-left">Article</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-left">Submitted</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($comments as $comment)
                <tr class="hover:bg-zinc-800/70" id="comment-{{ $comment->id }}">
                    <td class="px-6 py-4">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center font-semibold shrink-0">
                                {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-white">{{ $comment->user->name ?? 'Deleted user' }}</p>
                                <p class="text-sm text-zinc-400 mt-1 whitespace-pre-line break-words">{{ Str::limit($comment->body, 220) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($comment->article)
                            <a href="{{ route('news.public.show', $comment->article->slug) }}"
                               target="_blank" rel="noopener"
                               class="text-sm text-blue-400 hover:text-blue-500">
                                {{ Str::limit($comment->article->title, 50) }}
                            </a>
                        @else
                            <span class="text-sm text-zinc-600">Article removed</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php($status = $comment->status)
                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $status === 'approved' ? 'bg-emerald-500/20 text-emerald-400' : ($status === 'rejected' ? 'bg-red-500/20 text-red-400' : 'bg-orange-500/20 text-orange-400') }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-500">
                        {{ $comment->created_at->format('d M Y, H:i') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-3">
                            @if($status !== 'approved')
                                <button onclick="setStatus({{ $comment->id }}, 'approved')"
                                        title="Approve" class="text-emerald-400 hover:text-emerald-500">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                            @if($status !== 'rejected')
                                <button onclick="setStatus({{ $comment->id }}, 'rejected')"
                                        title="Reject" class="text-orange-400 hover:text-orange-500">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @endif
                            @if($status !== 'pending')
                                <button onclick="setStatus({{ $comment->id }}, 'pending')"
                                        title="Move back to pending" class="text-zinc-400 hover:text-zinc-300">
                                    <i class="fas fa-clock"></i>
                                </button>
                            @endif
                            <button onclick="deleteComment({{ $comment->id }})"
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
        {{ $comments->appends(request()->query())->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

async function setStatus(id, status) {
    const response = await fetch(`/news-comments/${id}`, {
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

function deleteComment(id) {
    showDeleteModal(`/news-comments/${id}`, 'Delete this comment permanently?');
}
</script>
@endpush
