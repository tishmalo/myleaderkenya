<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\PollRepositoryInterface;
use App\Models\Poll;
use App\Models\PollComment;
use App\Models\User;
use App\Support\HomepageCache;
use App\Support\PollPresenter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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
     * The poll sections the homepage renders, one per open poll the viewer may
     * take part in. A viewer who is not logged in only sees national polls;
     * logged-in viewers additionally see every poll scoped to their location.
     *
     * Only anonymous-safe data is cached: the lightweight shells (ids plus
     * audience columns). Whether the current viewer has voted, and whether
     * results are visible to them, is resolved per request so one visitor's
     * vote can never leak into another's page.
     *
     * @return list<array<string, mixed>>
     */
    public function homepagePolls(?User $viewer): array
    {
        $candidates = Cache::remember(
            HomepageCache::key('open-poll-candidates'),
            self::SHELL_TTL,
            fn (): array => $this->repository->openPollCandidates()->all()
        );

        $polls = [];

        foreach ($candidates as $candidate) {
            if (! $this->viewerEligible($candidate, $viewer)) {
                continue;
            }

            $poll = $this->repository->findForDisplay((int) $candidate['id']);

            // The cached shell can outlive the row if a poll is deleted or
            // deactivated between the cache write and this read.
            if ($poll === null) {
                continue;
            }

            $polls[] = $this->present($poll, $viewer);
        }

        return $polls;
    }

    /**
     * Whether the homepage should nudge this viewer to give their location.
     * Guests are allowed to browse national polls without one, so the prompt
     * is only for a logged-in member whose profile is missing a location, and
     * only when an open poll actually needs one.
     */
    public function needsLocationPrompt(?User $viewer): bool
    {
        if ($viewer === null || filled($viewer->county)) {
            return false;
        }

        return collect($this->openPollCandidates())
            ->contains(fn (array $candidate) => $candidate['audience_scope'] !== Poll::AUDIENCE_NATIONAL);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function openPollCandidates(): array
    {
        return Cache::remember(
            HomepageCache::key('open-poll-candidates'),
            self::SHELL_TTL,
            fn (): array => $this->repository->openPollCandidates()->all()
        );
    }

    /**
     * Can this viewer see a poll defined by these audience columns? National
     * polls are open to everyone; everything else needs a registered user
     * whose location matches the derived region - or, for a "members" poll,
     * any registered user with a location at all.
     *
     * @param  array<string, mixed>  $candidate
     */
    protected function viewerEligible(array $candidate, ?User $viewer): bool
    {
        $scope = $candidate['audience_scope'];

        if ($scope === Poll::AUDIENCE_NATIONAL) {
            return true;
        }

        if ($viewer === null) {
            return false;
        }

        return match ($scope) {
            Poll::AUDIENCE_MEMBERS => filled($viewer->county),
            Poll::AUDIENCE_COUNTY => $this->sameLocation(
                $viewer->county,
                $candidate['audience_county']
            ),
            Poll::AUDIENCE_CONSTITUENCY => $this->sameLocation($viewer->county, $candidate['audience_county'])
                && $this->sameLocation($viewer->constituency, $candidate['audience_constituency']),
            Poll::AUDIENCE_WARD => $this->sameLocation($viewer->county, $candidate['audience_county'])
                && $this->sameLocation($viewer->constituency, $candidate['audience_constituency'])
                && $this->sameLocation($viewer->ward, $candidate['audience_ward']),
            default => false,
        };
    }

    private function sameLocation(?string $viewerValue, mixed $audienceValue): bool
    {
        return filled($viewerValue)
            && Str::lower(trim((string) $viewerValue)) === Str::lower(trim((string) $audienceValue));
    }

    /**
     * The full presentation of a live poll, reachable by its shareable slug.
     * Returns null when no such poll is live, so the controller can 404.
     *
     * @return array<string, mixed>|null
     */
    public function presentPollBySlug(string $slug, ?User $viewer): ?array
    {
        $poll = $this->repository->findLivePollBySlug($slug);

        if ($poll === null) {
            return null;
        }

        return $this->present($poll, $viewer);
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(Poll $poll, ?User $viewer): array
    {
        $resultsArePublic = $poll->hasPublicResults();

        $votedOptionId = $viewer === null
            ? null
            : $this->repository->votedOptionId($poll->id, $viewer->id);

        // A voter sees the tally on polls they voted on, even before results
        // go public - unless the admin switched that off for the poll.
        // Everyone else only sees public results.
        $tallies = $resultsArePublic || ($votedOptionId !== null && $poll->show_results_to_voters)
            ? $this->repository->resultsFor($poll->id)
            : collect();

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

        if (! $this->viewerEligibleForPoll($poll, $user)) {
            throw ValidationException::withMessages([
                'option_id' => 'This poll is only open to voters in its area.',
            ]);
        }

        if (! $this->repository->optionExists($poll->id, $optionId)) {
            throw ValidationException::withMessages([
                'option_id' => 'That option is not part of this poll.',
            ]);
        }

        $this->repository->recordVote($poll->id, $optionId, $user->id);
    }

    /**
     * A live poll row may be reached with a URL even though the homepage would
     * not have offered it to this viewer, so the vote is gated again here.
     */
    protected function viewerEligibleForPoll(Poll $poll, User $user): bool
    {
        return $this->viewerEligible([
            'audience_scope' => $poll->audience_scope,
            'audience_county' => $poll->audience_county,
            'audience_constituency' => $poll->audience_constituency,
            'audience_ward' => $poll->audience_ward,
        ], $user);
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
