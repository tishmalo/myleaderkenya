<?php

namespace App\Services\Web;

use App\Models\Poll;
use App\Models\PollComment;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\User;
use App\Support\HomepageCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PollService
{
    /**
     * Short on purpose: the poll deadline is time-sensitive, so the cached
     * shell must not outlive the window it describes.
     */
    private const SHELL_TTL = 60;

    public function __construct(private readonly SpamFilterService $spamFilterService) {}

    /**
     * The poll shell shared by every visitor.
     *
     * Only anonymous-safe data is cached here. Whether the current viewer has
     * voted, and whether results are visible to them, is resolved per request
     * in homepagePoll() so one visitor's vote can never leak into another's
     * page.
     */
    public function activePollShell(): ?array
    {
        return Cache::remember(
            HomepageCache::key('active-poll'),
            self::SHELL_TTL,
            function (): ?array {
                $poll = Poll::query()
                    ->with('options.candidate.position')
                    ->where('status', Poll::STATUS_ACTIVE)
                    ->orderByDesc('ends_at')
                    ->first();

                if ($poll === null) {
                    return null;
                }

                return [
                    'id' => $poll->id,
                    'question' => $poll->question,
                    'poll_type' => $poll->poll_type,
                    'starts_at' => $poll->starts_at?->toIso8601String(),
                    'ends_at' => $poll->ends_at->toIso8601String(),
                    'reveal_results' => $poll->reveal_results,
                    'options' => $poll->options->map(fn (PollOption $option) => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'candidate' => $option->candidate ? [
                            'name' => $option->candidate->name,
                            'position' => $option->candidate->position->name ?? 'Aspirant',
                            'area' => $option->candidate->display_area ?? 'Kenya',
                            'party' => $option->candidate->politicalParty->abbreviation
                                ?? $option->candidate->politicalParty->name
                                ?? 'Independent',
                            'photo' => $option->candidate->profile_picture
                                ? \Illuminate\Support\Facades\Storage::url($option->candidate->profile_picture)
                                : null,
                            'url' => route('aspirants.show', $option->candidate),
                        ] : null,
                    ])->values(),
                ];
            }
        );
    }

    /**
     * The view model for the homepage section, or null when there is no poll
     * to show. Only the active poll is ever rendered.
     */
    public function homepagePoll(?User $viewer): ?array
    {
        $shell = $this->activePollShell();

        if ($shell === null) {
            return null;
        }

        $poll = Poll::query()
            ->withCount('votes')
            ->find($shell['id']);

        if ($poll === null) {
            return null;
        }

        $userId = $viewer?->id;

        $votedOptionId = $userId === null
            ? null
            : PollVote::query()
                ->where('poll_id', $poll->id)
                ->where('user_id', $userId)
                ->value('poll_option_id');

        $revealResults = $poll->hasPublicResults();

        return [
            'id' => $poll->id,
            'question' => $shell['question'],
            'poll_type' => $shell['poll_type'],
            'ends_at' => $shell['ends_at'],
            'starts_at' => $shell['starts_at'],
            'is_open' => $poll->isOpenForVoting(),
            // Distinguishes "has not opened yet" from "already closed", so the
            // homepage never claims a scheduled poll is finished.
            'has_started' => $poll->starts_at === null || $poll->starts_at->isPast(),
            'has_voted' => $votedOptionId !== null,
            'voted_option_id' => $votedOptionId,
            'reveal_results' => $revealResults,
            // Participation is public; the breakdown is not, until the deadline.
            'total_votes' => (int) $poll->votes_count,
            'results' => $revealResults
                ? $poll->results()->map(fn (array $row) => [
                    'option_id' => $row['option']->id,
                    'votes' => $row['votes'],
                    'percent' => $row['percent'],
                ])->keyBy('option_id')
                : collect(),
            'options' => $shell['options'],
        ] + [
            // Approved comments only, and read per request so a freshly
            // approved comment shows up without waiting on the shell cache.
            'approved_comments' => $this->approvedComments($poll)->map(fn (PollComment $comment) => [
                'author' => $comment->user?->name ?? 'Member',
                'body' => $comment->body,
                'created_at' => $comment->created_at?->diffForHumans(),
            ])->all(),
            'comment_count' => $this->approvedCommentCount($poll),
        ];
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

        $option = $poll->options()->whereKey($optionId)->first();

        if ($option === null) {
            throw ValidationException::withMessages([
                'option_id' => 'That option is not part of this poll.',
            ]);
        }

        DB::transaction(function () use ($poll, $option, $user) {
            PollVote::updateOrCreate(
                ['poll_id' => $poll->id, 'user_id' => $user->id],
                ['poll_option_id' => $option->id]
            );
        });
    }

    public function canComment(Poll $poll, ?User $user): bool
    {
        if ($user === null || ! $poll->hasVotedBy($user->id)) {
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

        return $poll->comments()->create([
            'user_id' => $user->id,
            'body' => $body,
            'status' => PollComment::STATUS_PENDING,
        ]);
    }

    public function approvedComments(Poll $poll)
    {
        return $poll->approvedComments()->with('user')->limit(20)->get();
    }

    public function approvedCommentCount(Poll $poll): int
    {
        return $poll->comments()->approved()->count();
    }
}
