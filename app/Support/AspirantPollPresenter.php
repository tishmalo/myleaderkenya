<?php

namespace App\Support;

use App\Models\AspirantPoll;
use Illuminate\Support\Collection;

class AspirantPollPresenter
{
    public static function format(AspirantPoll $poll, ?int $userId = null): array
    {
        $poll->loadMissing(['candidate.position', 'responses']);

        $totalResponses = $poll->responses->count();
        $selected = $userId
            ? $poll->responses->firstWhere('user_id', $userId)?->option_index
            : null;

        return [
            'id' => $poll->id,
            'question' => $poll->question,
            'scope_type' => $poll->scope_type,
            'scope_column' => $poll->scope_column,
            'scope_value' => $poll->scope_value,
            'location' => self::location($poll),
            'match' => self::matchTarget($poll),
            'status' => $poll->status,
            'published_at' => $poll->published_at,
            'total_responses' => $totalResponses,
            'selected_option_index' => $selected,
            'aspirant' => [
                'id' => $poll->candidate?->id,
                'name' => $poll->candidate?->name,
                'position' => $poll->candidate?->position?->name,
            ],
            'options' => self::options($poll, $totalResponses),
        ];
    }

    private static function location(AspirantPoll $poll): array
    {
        return [
            'country' => $poll->scope_type === 'national' ? 'Kenya' : ($poll->candidate?->country ?: 'Kenya'),
            'county' => $poll->candidate?->county,
            'constituency' => $poll->candidate?->constituency,
            'ward' => $poll->candidate?->ward,
        ];
    }

    private static function matchTarget(AspirantPoll $poll): array
    {
        return [
            'level' => $poll->scope_type,
            'field' => $poll->scope_column,
            'value' => $poll->scope_value,
            'label' => self::matchLabel($poll),
        ];
    }

    private static function matchLabel(AspirantPoll $poll): string
    {
        if ($poll->scope_type === 'national') {
            return 'Kenya';
        }

        return $poll->scope_value ?: ($poll->candidate?->{$poll->scope_column} ?? ucfirst((string) $poll->scope_type));
    }

    private static function options(AspirantPoll $poll, int $totalResponses): Collection
    {
        return collect($poll->options ?? [])->map(function (string $option, int $index) use ($poll, $totalResponses): array {
            $count = $poll->responses->where('option_index', $index)->count();

            return [
                'index' => $index,
                'label' => $option,
                'response_count' => $count,
                'response_percent' => $totalResponses > 0 ? round(($count / $totalResponses) * 100) : 0,
            ];
        })->values();
    }
}
