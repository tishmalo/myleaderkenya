<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\PollRepositoryInterface;
use App\Models\Poll;
use App\Models\PollComment;
use App\Models\User;
use App\Support\HomepageCache;
use App\Support\PollPresenter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Business rules for the public poll: when a poll is open, who may vote, and
 * who may join the discussion. Every query lives in the repository and every
 * formatting decision lives in the presenter.
 */
class PollService
{
    /**
     * Short on purpose: the poll deadline is time-sensitive, so the cached
     * shell must not outlive the window it describes.
     */
    private const SHELL_TTL = 60;

    public function __construct(
        private readonly PollRepositoryInterface $repository,
        private readonly SpamFilterService $spamFilterService
    ) {}

    /**
     * The section the homepage renders, or null when there is no poll to show.
     *
     * Only anonymous-safe data is cached. Whether the current viewer has voted,
     * and whether results are visible to them, is resolved per request so one
     * visitor's vote can never leak into another's page.
     */
    public function homepagePoll(?User $viewer): ?array
    {
        // Only the id is cached, and only briefly: the deadline moves, so a
        // longer TTL could show a poll as open after it has closed.
        $pollId = Cache::remember(
            HomepageCache::key('active-poll'),
            self::SHELL_TTL,
            fn (): ?int => $this->repository->activePollId()
        );

        if ($pollId === null) {
            return null;
        }

        $poll = $this->repository->findForDisplay($pollId);

        // The cached id can outlive the row if a poll is deleted or deactivated.
        if ($poll === null) {
            Cache::forget(HomepageCache::key('active-poll'));

            return null;
        }

        $resultsArePublic = $poll->hasPublicResults();

        $tallies = $resultsArePublic
            ? $this->repository->resultsFor($poll->id)
            : collect();

        $votedOptionId = $viewer === null
            ? null
            : $this->repository->votedOptionId($poll->id, $viewer->id);

        return PollPresenter::homepage(
            $poll,
            $poll->options,
            $tallies,
            $this->repository->approvedComments($poll->id),
            (int) $poll->votes_count,
            $this->repository->approvedCommentCount($poll->id),
            $votedOptionId,
            $viewer !== null
        );
    }

    /**
     * Records a vote. A second vote from the same account replaces the first
     * rather than failing, which is friendlier when someone taps the wrong
     * option first.
     */
    public function castVote(Poll $poll, int $optionId, User $user): void
    {
        if (! $poll->isOpenForVoting()) {
            throw ValidationException::withMessages([
                'option_id' => 'This poll is closed and no longer accepting votes.',
            ]);
        }

        if (! $this->repository->optionExists($poll->id, $optionId)) {
            throw ValidationException::withMessages([
                'option_id' => 'That option is not part of this poll.',
            ]);
        }

        $this->repository->recordVote($poll->id, $optionId, $user->id);
    }

    public function canComment(Poll $poll, ?User $user): bool
    {
        if ($user === null || ! $this->repository->votedOptionId($poll->id, $user->id)) {
            return false;
        }

        // The discussion only opens once voting is over and results are public.
        // The form is hidden in that state, so enforce it server-side too,
        // otherwise a direct POST could comment on an open or hidden poll.
        return ! $poll->isOpenForVoting() && $poll->hasPublicResults();
    }

    public function createComment(Poll $poll, User $user, string $body): PollComment
    {
        if (! $this->canComment($poll, $user)) {
            throw ValidationException::withMessages([
                'body' => 'Vote on the poll before joining the discussion.',
            ]);
        }

        $rejection = $this->spamFilterService->inspect(['body' => $body, 'user_id' => $user->id]);

        if ($rejection !== null) {
            $this->spamFilterService->recordSample(
                ['body' => $body, 'user_id' => $user->id],
                $rejection,
                request()->ip(),
                'poll_comment'
            );

            throw ValidationException::withMessages([
                'body' => 'Your comment was not accepted. Please review and try again.',
            ]);
        }

        return $this->repository->saveComment($poll, $user, $body);
    }
}
