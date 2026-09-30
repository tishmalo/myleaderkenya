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
        $polls = $this->pollService->paginate(15);

        return view('polls.index', [
            'paginator' => $polls,
            'rows' => \App\Support\AdminPollPresenter::index(collect($polls->items()))['polls'],
            'pendingCommentCount' => $this->pollService->comments(['status' => PollComment::STATUS_PENDING], 1)->total(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('polls.create', [
            'form' => \App\Support\AdminPollPresenter::form(
                null,
                $request->old() ?: []
            ),
        ]);
    }

    /**
     * JSON backing for the bulk aspirant picker in the poll form. Given the
     * position and/or location the admin has chosen, returns the matching
     * location options and the approved aspirants in that scope.
     */
    public function aspirantPicker(Request $request): JsonResponse
    {
        $filters = [
            'position_id' => $request->integer('position_id') ?: null,
            'county' => $request->string('county')->toString() ?: null,
            'constituency' => $request->string('constituency')->toString() ?: null,
            'ward' => $request->string('ward')->toString() ?: null,
            'search' => $request->string('search')->toString() ?: null,
        ];

        $data = $this->pollService->pickerData($filters);

        return response()->json([
            'positions' => \App\Support\AdminPollPresenter::pickerPositions($data['positions']),
            'counties' => $data['counties']->values()->all(),
            'constituencies' => $data['constituencies']->values()->all(),
            'wards' => $data['wards']->values()->all(),
            'candidates' => \App\Support\AdminPollPresenter::pickerCandidates($data['candidates']),
        ]);
    }

    public function store(PollStoreRequest $request): RedirectResponse
    {
        $poll = $this->pollService->create($request->validated(), $request->user());

        return redirect()
            ->route('polls.index')
            ->with('success', 'Poll "'.$poll->question.'" created.');
    }

    public function edit(Request $request, int $poll): View
    {
        $record = $this->pollService->find($poll);

        abort_if($record === null, 404);

        return view('polls.edit', [
            'poll' => $record,
            'results' => \App\Support\AdminPollPresenter::results($this->pollService->resultsFor($record->id)),
            'form' => \App\Support\AdminPollPresenter::form(
                $record,
                $request->old() ?: []
            ),
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
        $rows = \App\Support\AdminPollPresenter::results($this->pollService->resultsFor($poll->id));

        return response()->json([
            'total' => $rows['total_votes_raw'],
            'results' => $rows['rows'],
        ]);
    }

    public function pendingComments(Request $request): View
    {
        $filters = [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'poll_id' => $request->integer('poll_id') ?: null,
        ];

        $comments = $this->pollService->comments($filters, 25);

        return view('polls.comments', [
            'paginator' => $comments,
            'rows' => \App\Support\AdminPollPresenter::commentRows(collect($comments->items())),
            'statusTabs' => \App\Support\AdminPollPresenter::commentTabs(
                $filters['status'],
                route('poll-comments.index')
            ),
            'currentStatus' => $filters['status'],
            'search' => $request->query('search'),
            'queryString' => collect($request->query())->all(),
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
