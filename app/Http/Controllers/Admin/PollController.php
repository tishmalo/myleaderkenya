<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PollStoreRequest;
use App\Http\Requests\Admin\PollUpdateRequest;
use App\Models\Poll;
use App\Models\PollComment;
use App\Services\Admin\PollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PollController extends Controller
{
    public function __construct(private readonly PollService $pollService) {}

    public function index(Request $request): View
    {
        return view('polls.index', [
            'polls' => $this->pollService->paginate(15),
            'pendingCommentCount' => $this->pollService->comments(['status' => PollComment::STATUS_PENDING], 1)->total(),
        ]);
    }

    public function create(): View
    {
        return view('polls.create', [
            'poll' => null,
            'candidates' => $this->pollService->approvedCandidates(),
        ]);
    }

    public function store(PollStoreRequest $request): RedirectResponse
    {
        $poll = $this->pollService->create($request->validated(), $request->user());

        return redirect()
            ->route('polls.index')
            ->with('success', 'Poll "'.$poll->question.'" created.');
    }

    public function edit(int $poll): View
    {
        $record = $this->pollService->find($poll);

        abort_if($record === null, 404);

        return view('polls.edit', [
            'poll' => $record,
            'results' => $record->results(),
            'candidates' => $this->pollService->approvedCandidates(),
            'comments' => $this->pollService->commentsForPoll($record->id),
        ]);
    }

    public function update(PollUpdateRequest $request, int $poll): RedirectResponse
    {
        $record = $this->pollService->find($poll);

        abort_if($record === null, 404);

        $this->pollService->update($record, $request->validated());

        return redirect()
            ->route('polls.edit', $record)
            ->with('success', 'Poll updated.');
    }

    public function destroy(Poll $poll): JsonResponse
    {
        $this->pollService->delete($poll);

        return response()->json(['message' => 'Poll deleted.']);
    }

    public function results(Poll $poll): JsonResponse
    {
        $poll->load('options.candidate.position');

        return response()->json([
            'total' => $poll->totalVotes(),
            'results' => $poll->results()->map(fn (array $row) => [
                'option_id' => $row['option']->id,
                'label' => $row['option']->label,
                'votes' => $row['votes'],
                'percent' => $row['percent'],
            ])->all(),
        ]);
    }

    public function pendingComments(Request $request): View
    {
        $filters = [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'poll_id' => $request->integer('poll_id') ?: null,
        ];

        return view('polls.comments', [
            'comments' => $this->pollService->comments($filters, 25),
        ]);
    }

    public function moderateComment(Request $request, PollComment $pollComment): JsonResponse
    {
        $status = $request->validate([
            'status' => ['required', 'in:'.implode(',', [
                PollComment::STATUS_PENDING,
                PollComment::STATUS_APPROVED,
                PollComment::STATUS_REJECTED,
            ])],
        ])['status'];

        $this->pollService->moderateComment($pollComment, $status, $request->user());

        return response()->json(['message' => 'Comment '.$status.'.']);
    }

    public function destroyComment(PollComment $pollComment): JsonResponse
    {
        $this->pollService->deleteComment($pollComment);

        return response()->json(['message' => 'Comment deleted.']);
    }
}
