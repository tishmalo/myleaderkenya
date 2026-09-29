<?php

namespace App\Support;

use App\Models\Poll;
use App\Models\PollComment;
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
    public static function form(?Poll $poll, array $old, Collection $candidates): array
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
            'candidate_groups' => self::candidateGroups($candidates),
            'candidate_groups_json' => self::candidateGroupsJson($candidates),
        ];
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
        return $poll?->options->map(fn ($option) => [
            'id' => $option->id,
            'label' => $option->label,
            'candidate_id' => $option->candidate_id,
        ])->all() ?? [];
    }

    /**
     * Normalises option rows into at least two template rows so the admin can
     * always add or edit without hitting an empty list.
     */
    private static function normalizeOptions(array $options): array
    {
        $empty = ['id' => null, 'label' => '', 'candidate_id' => null];

        if ($options === []) {
            return [$empty, $empty];
        }

        return collect($options)->map(fn ($option) => [
            'id' => $option['id'] ?? null,
            'label' => $option['label'] ?? '',
            'candidate_id' => $option['candidate_id'] ?? null,
        ])->values()->all();
    }

    private static function candidateGroups(Collection $candidates): Collection
    {
        return $candidates
            ->groupBy(fn ($candidate) => $candidate->position?->name ?? 'Aspirants')
            ->map(fn (Collection $group, string $name) => [
                'label' => $name,
                'candidates' => $group->map(fn ($candidate) => [
                    'id' => $candidate->id,
                    'name' => $candidate->name.($candidate->politicalParty?->abbreviation
                        ? ' ('.$candidate->politicalParty->abbreviation.')'
                        : ''),
                ])->values(),
            ])->values();
    }

    private static function candidateGroupsJson(Collection $candidates): string
    {
        return json_encode(
            self::candidateGroups($candidates),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
    }
}