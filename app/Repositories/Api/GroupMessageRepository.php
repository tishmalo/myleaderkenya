<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\GroupMessageRepositoryInterface;
use App\Models\GroupMessage;
use App\Support\AspirantPollPresenter;
use Illuminate\Support\Collection;

class GroupMessageRepository implements GroupMessageRepositoryInterface
{
    public function create(array $data): GroupMessage
    {
        return GroupMessage::create($data);
    }

    public function getGroupMessages(int $groupId, ?int $userId = null): Collection
    {
        return GroupMessage::with(['poll.candidate.position', 'poll.responses'])
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

        $payload['poll'] = AspirantPollPresenter::format($message->poll, $userId);
        return $payload;
    }
}
