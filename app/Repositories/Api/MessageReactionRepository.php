<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\MessageReactionRepositoryInterface;
use App\Models\MessageReaction;

class MessageReactionRepository implements MessageReactionRepositoryInterface
{
    public function create(array $data): MessageReaction
    {
        return MessageReaction::create($data);
    }

    public function deleteUserReaction(int $messageId, int $userId): void
    {
        MessageReaction::where('message_id', $messageId)
            ->where('user_id', $userId)
            ->delete();
    }
}
