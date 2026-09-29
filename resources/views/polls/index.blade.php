@extends('layouts.app')

@section('page_title', 'Polls')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-chart-simple text-emerald-500"></i>
            Polls
        </h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('poll-comments.index') }}"
               class="bg-zinc-800 hover:bg-zinc-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
                <i class="fas fa-comments"></i> Comments
                @if($pendingCommentCount ?? 0)
                    <span class="px-2 py-0.5 rounded-full bg-orange-500/20 text-orange-400 text-xs">{{ $pendingCommentCount }}</span>
                @endif
            </a>
            <a href="{{ route('polls.create') }}"
               class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
                <i class="fas fa-plus"></i> New Poll
            </a>
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-zinc-800 bg-zinc-900/60 px-5 py-4 text-sm text-zinc-400">
        <i class="fas fa-circle-info text-emerald-500"></i>
        Only one poll can be active at a time, because the homepage shows a single poll directly above Public Sentiment.
        Activating a poll automatically closes any other that was live.
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr>
                    <th class="px-6 py-4 text-left">Question</th>
                    <th class="px-6 py-4 text-left">Type</th>
                    <th class="px-6 py-4 text-left">Closes</th>
                    <th class="px-6 py-4 text-center">Votes</th>
                    <th class="px-6 py-4 text-center">Public Results</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($rows as $row)
                <tr class="hover:bg-zinc-800/70">
                    <td class="px-6 py-4">
                        <p class="font-medium text-white">{{ $row['question'] }}</p>
                        <p class="text-xs text-zinc-500">{{ $row['option_count'] }} options</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-400">
                        {{ $row['type_label'] }}
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-400">{{ $row['closes_at'] }}</td>
                    <td class="px-6 py-4 text-center text-sm text-zinc-300">{{ $row['total_votes_label'] }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $row['results_visibility']['class'] }}">{{ $row['results_visibility']['label'] }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $row['status_badge_class'] }}">{{ $row['status_label'] }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ $row['edit_url'] }}" class="text-blue-400 hover:text-blue-500 mx-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deletePoll({{ $row['id'] }}, '{{ $row['delete_question'] }}')"
                                class="text-red-400 hover:text-red-500 mx-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-16 text-zinc-500">No polls yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-center">
        {{ $paginator->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function deletePoll(id, question) {
    showDeleteModal(`/admin/polls/${id}`, `Delete poll <strong>${question}</strong> and all of its votes?`);
}
</script>
@endpush
