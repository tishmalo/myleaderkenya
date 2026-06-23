<?php

namespace App\Contracts\Repositories\Api;

use App\Models\MessageReaction;

interface MessageReactionRepositoryInterface
{
    public function create(array $data): MessageReaction;

    public function deleteUserReaction(int $messageId, int $userId): void;
}
