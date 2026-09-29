<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Services\Web\PollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PollCommentController extends Controller
{
    public function __construct(private readonly PollService $pollService) {}

    public function store(Request $request, Poll $poll): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $this->pollService->createComment($poll, $request->user(), $validated['body']);

        return redirect()
            ->route('landing')
            ->with('poll_comment_notice', 'Thanks — your comment is awaiting moderation.');
    }
}
