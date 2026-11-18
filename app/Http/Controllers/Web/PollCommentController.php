<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StorePollCommentRequest;
use App\Models\Poll;
use App\Services\Web\PollService;
use Illuminate\Http\RedirectResponse;

class PollCommentController extends Controller
{
    public function __construct(private readonly PollService $pollService) {}

    public function store(StorePollCommentRequest $request, Poll $poll): RedirectResponse
    {
        $this->pollService->createComment($poll, $request->user(), $request->body());

        return redirect()
            ->route('landing')
            ->with('poll_comment_notice', 'Thanks — your comment is awaiting moderation.');
    }
}
