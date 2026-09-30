<?php

namespace App\Support;

use App\Models\Candidate;
use App\Models\Poll;
use App\Models\PollComment;
use App\Models\Position;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Shapes polls for the admin screens.
 *
 * The index used to call $poll->options()->count() inside the Blade, which
 * meant one extra query per row, and the form and comment lists reached into
 * models mid-render. Every value here is a plain, ready-to-print array so the
 * views stay declarative. Performs no queries of its own.
 */
class AdminPollPresenter
{
    public static function rows(Poll $poll): array
    {
        return [
            'id' => $poll->id,
            'question' => $poll->question,
            'type_label' => $poll->poll_type === Poll::TYPE_POLITICAL ? 'Political' : 'Words',
            'option_count' => (int) ($poll->options_count ?? 0),
            'closes_at' => $poll->ends_at->format('d M Y, g:ia'),
            'total_votes_label' => number_format((int) ($poll->votes_count ?? 0)),
            'results_visibility' => self::resultsVisibility($poll),
            'status_label' => ucfirst($poll->status),
            'status_badge_class' => match ($poll->status) {
                Poll::STATUS_ACTIVE => 'bg-emerald-500/20 text-emerald-400',
                Poll::STATUS_CLOSED => 'bg-zinc-700/60 text-zinc-300',
                default => 'bg-orange-500/20 text-orange-400',
            },
            'edit_url' => route('polls.edit', $poll),
            'delete_question' => addslashes($poll->question),
        ];
    }

    /**
     * @param  Collection<int, Poll>  $polls
     */
    public static function index(Collection $polls): array
    {
        return [
            'polls' => $polls->map(fn (Poll $poll) => self::rows($poll))->all(),
            'has_polls' => $polls->isNotEmpty(),
        ];
    }

    /**
     * The create form has no poll and the edit form has one; the request's
     * previous input string wins over the stored poll so a failed submit
     * redraws exactly what the admin typed.
     *
     * @param  array  $old  Illuminate\Http\Request::old(), may be empty
     */
    public static function form(?Poll $poll, array $old): array
    {
        $options = $old['options'] ?? self::storedOptions($poll);
        $startsAt = $poll?->starts_at;
        $endsAt = $poll?->ends_at;

        return [
            'action' => $poll ? route('polls.update', $poll) : route('polls.store'),
            'method' => $poll ? 'PUT' : 'POST',
            'editing' => $poll !== null,
            'submit_label' => $poll ? 'Save Poll' : 'Create Poll',
            'question' => $old['question'] ?? ($poll?->question ?? ''),
            'selected_type' => $old['poll_type'] ?? ($poll?->poll_type ?? 'words'),
            'status_selected' => $old['status'] ?? ($poll?->status ?? 'draft'),
            'starts_at_value' => $old['starts_at'] ?? ($startsAt ? $startsAt->format('Y-m-d\TH:i') : ''),
            'ends_at_value' => $old['ends_at'] ?? ($endsAt ? $endsAt->format('Y-m-d\TH:i') : ''),
            'reveal_results_checked' => array_key_exists('reveal_results', $old)
                ? (bool) $old['reveal_results']
                : (bool) ($poll?->reveal_results ?? true),
            'existing_options' => self::normalizeOptions($options),
            'aspirant_picker_url' => route('polls.aspirants'),
            'audience' => self::audience($poll),
        ];
    }

    /**
     * The derived audience, shown read-only. Created when the poll is saved
     * from the aspirants it is linked to, so a stored poll reports its scope
     * while a fresh one simply explains where the scope comes from.
     *
     * @return array{draft:bool,label:string|null,scope_key:string|null,county:string|null,constituency:string|null,ward:string|null}
     */
    private static function audience(?Poll $poll): array
    {
        if ($poll === null) {
            return [
                'draft' => true,
                'label' => 'Calculated from the aspirants you add.',
                'scope_key' => null,
                'county' => null,
                'constituency' => null,
                'ward' => null,
            ];
        }

        $part = fn (?string $value) => filled($value) ? $value : null;
        $area = trim(implode(', ', array_filter([
            $part($poll->audience_county),
            $part($poll->audience_constituency),
            $part($poll->audience_ward),
        ])));

        $labels = [
            Poll::AUDIENCE_NATIONAL => 'Everyone',
            Poll::AUDIENCE_MEMBERS => 'Logged-in members with a location',
            Poll::AUDIENCE_COUNTY => 'Voters in '.$poll->audience_county,
            Poll::AUDIENCE_CONSTITUENCY => 'Voters in '.$area,
            Poll::AUDIENCE_WARD => 'Voters in '.$area,
        ];

        return [
            'draft' => false,
            'label' => $labels[$poll->audience_scope] ?? 'Logged-in members',
            'scope_key' => $poll->audience_scope,
            'county' => $part($poll->audience_county),
            'constituency' => $part($poll->audience_constituency),
            'ward' => $part($poll->audience_ward),
        ];
    }

    /**
     * @param  Collection<int, Position>  $positions
     * @return array<int, array{id:int,name:string}>
     */
    public static function pickerPositions(Collection $positions): array
    {
        return $positions->map(fn ($position) => [
            'id' => (int) $position->id,
            'name' => $position->name,
        ])->values()->all();
    }

    /**
     * @param  Collection<int, Candidate>  $candidates
     * @return array<int, array{id:int,name:string,party:string|null,area:string|null,badge:string}>
     */
    public static function pickerCandidates(Collection $candidates): array
    {
        return $candidates->map(function ($candidate) {
            $party = $candidate->politicalParty?->abbreviation ?: $candidate->politicalParty?->name;
            $area = $candidate->getDisplayAreaAttribute();

            return [
                'id' => (int) $candidate->id,
                'name' => $candidate->name,
                'party' => $party,
                'area' => $area,
                'badge' => trim(implode(' · ', array_filter([
                    $candidate->position?->name,
                    $party,
                    $area,
                ]))),
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, array{option_id:int,label:string,votes:int,percent:int}>  $results
     */
    public static function results(Collection $results): array
    {
        $total = (int) $results->sum('votes');

        return [
            'total_votes_label' => number_format($total).' '.Str::plural('vote', $total),
            'total_votes_raw' => $total,
            'has_rows' => $results->isNotEmpty(),
            'rows' => $results->map(function (array $row) {
                $votes = (int) $row['votes'];

                return [
                    'option_id' => (int) $row['option_id'],
                    'label' => $row['label'],
                    'percent' => (int) $row['percent'],
                    'votes' => $votes,
                    'votes_label' => number_format($votes),
                ];
            })->all(),
        ];
    }

    /**
     * @param  Collection<int, PollComment>  $comments
     */
    public static function commentRows(Collection $comments): array
    {
        return $comments->map(fn (PollComment $comment) => [
            'id' => $comment->id,
            'author' => $comment->user?->name ?? 'Deleted user',
            'initial' => Str::upper(Str::substr($comment->user?->name ?? '?', 0, 1)),
            'excerpt' => Str::limit($comment->body, 220),
            'poll_question' => Str::limit($comment->poll?->question ?? '', 50),
            'poll_edit_url' => $comment->poll ? route('polls.edit', $comment->poll) : null,
            'poll_removed' => $comment->poll === null,
            'status' => $comment->status,
            'status_label' => ucfirst($comment->status),
            'status_badge_class' => match ($comment->status) {
                PollComment::STATUS_APPROVED => 'bg-emerald-500/20 text-emerald-400',
                PollComment::STATUS_REJECTED => 'bg-red-500/20 text-red-400',
                default => 'bg-orange-500/20 text-orange-400',
            },
            'submitted_at' => $comment->created_at?->format('d M Y, H:i') ?? '',
            'can_approve' => $comment->status !== PollComment::STATUS_APPROVED,
            'can_reject' => $comment->status !== PollComment::STATUS_REJECTED,
            'can_reopen' => $comment->status !== PollComment::STATUS_PENDING,
        ])->all();
    }

    /**
     * Status filter tabs for the comments screen.
     *
     * @return array<int, array{url:string,label:string,active:bool}>
     */
    public static function commentTabs(?string $current, string $baseUrl): array
    {
        $current ??= '';

        return collect([
            '' => 'All',
            PollComment::STATUS_PENDING => 'Pending',
            PollComment::STATUS_APPROVED => 'Approved',
            PollComment::STATUS_REJECTED => 'Rejected',
        ])->map(fn (string $label, string $value) => [
            'url' => $value === '' ? $baseUrl : $baseUrl.'?status='.$value,
            'label' => $label,
            'active' => $current === $value,
        ])->values()->all();
    }

    private static function resultsVisibility(Poll $poll): array
    {
        if ($poll->hasPublicResults()) {
            return ['label' => 'Visible', 'class' => 'bg-emerald-500/20 text-emerald-400'];
        }

        if ($poll->reveal_results) {
            return ['label' => 'At deadline', 'class' => 'bg-orange-500/20 text-orange-400'];
        }

        return ['label' => 'Never', 'class' => 'bg-zinc-700/60 text-zinc-300'];
    }

    private static function storedOptions(?Poll $poll): array
    {
        return $poll?->options->map(function ($option) {
            $candidate = $option->candidate;
            $party = $candidate?->politicalParty?->abbreviation ?: $candidate?->politicalParty?->name;

            return [
                'id' => $option->id,
                'label' => $option->label,
                'candidate_id' => $option->candidate_id,
                'candidate_name' => $candidate?->name,
                'candidate_badge' => $candidate
                    ? trim(implode(' · ', array_filter([$candidate->position?->name, $party, $candidate->getDisplayAreaAttribute()])))
                    : null,
            ];
        })->all() ?? [];
    }

    /**
     * Normalises option rows into at least two template rows so the admin can
     * always add or edit without hitting an empty list.
     */
    private static function normalizeOptions(array $options): array
    {
        $empty = ['id' => null, 'label' => '', 'candidate_id' => null, 'candidate_name' => null, 'candidate_badge' => null];

        if ($options === []) {
            return [$empty, $empty];
        }

        return collect($options)->map(fn ($option) => [
            'id' => $option['id'] ?? null,
            'label' => $option['label'] ?? '',
            'candidate_id' => $option['candidate_id'] ?? null,
            'candidate_name' => $option['candidate_name'] ?? null,
            'candidate_badge' => $option['candidate_badge'] ?? null,
        ])->values()->all();
    }
}
