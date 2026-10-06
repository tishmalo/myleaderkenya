<?php

namespace App\Support;

use App\Models\Poll;
use App\Models\PollComment;
use App\Models\PollOption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Turns a poll into the flat, already-formatted array the homepage section
 * renders.
 *
 * This is the only place that knows how a poll looks on screen: number
 * formatting, plurals, dates and the scheduled/open/closed wording all live
 * here so the Blade stays declarative and never reaches back into the models.
 * It performs no queries of its own.
 */
class PollPresenter
{
    /**
     * @param  Collection<int, array{option_id: int, votes: int, percent: int}>  $tallies
     * @param  Collection<int, PollComment>  $comments
     * @param  Collection<int, PollOption>  $options
     */
    public static function homepage(
        Poll $poll,
        Collection $options,
        Collection $tallies,
        Collection $comments,
        int $totalVotes,
        int $commentCount,
        ?int $votedOptionId,
        bool $isAuthenticated
    ): array {
        $resultsArePublic = $poll->hasPublicResults();
        $isOpen = $poll->isOpenForVoting();
        $hasVoted = $votedOptionId !== null;
        $isScheduled = ! $isOpen && ! self::hasStarted($poll);
        // A voter sees the tally on polls they voted on, even before results
        // go public - unless the admin switched that off for the poll - and
        // may change their vote while the poll is open.
        $resultsVisible = $resultsArePublic || ($hasVoted && $poll->show_results_to_voters);

        $byOption = $tallies->keyBy('option_id');

        return [
            'id' => $poll->id,
            'slug' => $poll->slug,
            'section_label' => $poll->poll_type === Poll::TYPE_POLITICAL ? 'Vote Your Candidate' : 'Have Your Say',
            'question' => $poll->question,
            'status_line' => self::statusLine($poll, $isOpen, $isScheduled, $hasVoted),
            'vote_action' => route('poll.vote', $poll->id),
            'comment_action' => route('poll.comments.store', $poll->id),
            'share_url' => route('poll.show', $poll->slug),
            'share_og_image' => self::shareOgImage($options),
            'share_created_at' => ($poll->created_at?->toIso8601String()) ?? now()->toIso8601String(),
            'grid_class' => $poll->poll_type === Poll::TYPE_POLITICAL ? 'poll-grid is-political' : 'poll-grid',
            'is_authenticated' => $isAuthenticated,
            'can_vote' => $isOpen && $isAuthenticated,
            'prompts_login' => $isOpen && ! $hasVoted && ! $isAuthenticated,
            'confirms_vote' => $hasVoted && $isOpen,
            'total_votes_label' => number_format($totalVotes).' '.Str::plural('vote', $totalVotes).' cast',
            'results_locked' => ! $resultsVisible,
            'options' => $options->map(fn (PollOption $option) => self::option(
                $option,
                $byOption,
                $votedOptionId,
                $resultsVisible
            ))->all(),
            'comment_count' => $commentCount,
            'comments' => $comments->map(fn (PollComment $comment) => [
                'author' => $comment->user?->name ?? 'Member',
                'body' => $comment->body,
                'created_ago' => $comment->created_at?->diffForHumans() ?? '',
            ])->all(),
            // Matches Web\PollService::canComment() so the form is only offered
            // where the endpoint would actually accept a submission.
            'can_comment' => $isAuthenticated
                && $hasVoted
                && ! $isOpen
                && $resultsArePublic,
            'prompt_comment_login' => ! $isAuthenticated,
        ];
    }

    private static function option(
        PollOption $option,
        Collection $byOption,
        ?int $votedOptionId,
        bool $showTally
    ): array {
        $tally = $byOption->get($option->id);
        $votes = (int) ($tally['votes'] ?? 0);

        return [
            'id' => $option->id,
            'label' => $option->label,
            'is_chosen' => $votedOptionId === $option->id,
            'is_political' => $option->candidate !== null,
            'initial' => Str::upper(Str::substr((string) $option->label, 0, 1)),
            'meta' => $option->candidate?->position?->name ?? 'Aspirant',
            'area' => $option->candidate?->display_area ?? 'Kenya',
            'party' => self::party($option),
            'profile_url' => $option->candidate ? route('aspirants.show', $option->candidate) : null,
            'photo_url' => $option->candidate?->profile_picture
                ? Storage::url($option->candidate->profile_picture)
                : null,
            'show_tally' => $showTally,
            'percent' => (int) ($tally['percent'] ?? 0),
            'votes_label' => number_format($votes).' '.Str::plural('vote', $votes),
        ];
    }

    private static function party(PollOption $option): string
    {
        return $option->candidate?->politicalParty?->abbreviation
            ?? $option->candidate?->politicalParty?->name
            ?? 'Independent';
    }

    private static function shareOgImage(Collection $options): string
    {
        $firstPhoto = $options
            ->map(fn (PollOption $option) => $option->candidate?->profile_picture)
            ->filter()
            ->first();

        return $firstPhoto
            ? Storage::url($firstPhoto)
            : asset('images/myleader.png');
    }

    private static function hasStarted(Poll $poll): bool
    {
        return $poll->starts_at === null || $poll->starts_at->isPast();
    }

    private static function statusLine(Poll $poll, bool $isOpen, bool $isScheduled, bool $hasVoted): string
    {
        $closes = $poll->ends_at->format('j M Y, g:ia');

        if ($isScheduled) {
            return 'Voting opens '.$poll->starts_at->format('j M Y, g:ia').' and closes '.$closes.'.';
        }

        if ($isOpen) {
            return $hasVoted
                ? 'Voting closes '.$closes.'. You can change your vote until then.'
                : 'Voting closes '.$closes.'. Results unlock when the poll closes.';
        }

        return 'This poll closed '.$closes.'.';
    }
}
