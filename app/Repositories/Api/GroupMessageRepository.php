<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\GroupMessageRepositoryInterface;
use App\Models\GroupMessage;
use Illuminate\Support\Collection;

class GroupMessageRepository implements GroupMessageRepositoryInterface
{
    public function create(array $data): GroupMessage
    {
        return GroupMessage::create($data);
    }

    public function getGroupMessages(int $groupId, ?int $userId = null): Collection
    {
        return GroupMessage::with(['poll.responses'])
            ->where('group_id', $groupId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (GroupMessage $message): array => $this->formatMessage($message, $userId));
    }

    private function formatMessage(GroupMessage $message, ?int $userId): array
    {
        $payload = [
            'id' => $message->id,
            'type' => $message->message_type ?? 'text',
            'username' => $message->username,
            'message' => $message->message,
            'latitude' => $message->latitude,
            'longitude' => $message->longitude,
            'created_at' => $message->created_at,
        ];

        if (($message->message_type ?? null) !== 'poll' || ! $message->poll) {
            return $payload;
        }

        $poll = $message->poll;
        $totalResponses = $poll->responses->count();
        $selected = $userId
            ? $poll->responses->firstWhere('user_id', $userId)?->option_index
            : null;

        $payload['poll'] = [
            'id' => $poll->id,
            'question' => $poll->question,
            'scope_type' => $poll->scope_type,
            'scope_value' => $poll->scope_value,
            'status' => $poll->status,
            'total_responses' => $totalResponses,
            'selected_option_index' => $selected,
            'options' => collect($poll->options ?? [])->map(function (string $option, int $index) use ($poll, $totalResponses): array {
                $count = $poll->responses->where('option_index', $index)->count();

                return [
                    'index' => $index,
                    'label' => $option,
                    'response_count' => $count,
                    'response_percent' => $totalResponses > 0 ? round(($count / $totalResponses) * 100) : 0,
                ];
            })->values(),
        ];

        return $payload;
    }
}