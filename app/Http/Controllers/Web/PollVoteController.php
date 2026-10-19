<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Services\Web\PollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PollVoteController extends Controller
{
    public function __construct(private readonly PollService $pollService) {}

    /**
     * The lookup is scoped to live polls, so a visitor cannot post to a draft
     * or closed poll by guessing an id in the URL.
     */
    public function store(Request $request, Poll $poll): RedirectResponse
    {
        abort_unless($poll->status === Poll::STATUS_ACTIVE, 404);

        $validated = $request->validate([
            'option_id' => ['required', 'integer'],
        ]);

        $this->pollService->castVote($poll, (int) $validated['option_id'], $request->user());

        // Back where the vote was cast, so a reader on the polls page stays
        // there instead of being dropped on the landing page.
        return redirect()
            ->back(302, [], route('polls.public'))
            ->with('poll_notice', 'Your vote has been recorded. Results unlock when the poll closes.');
    }
}
