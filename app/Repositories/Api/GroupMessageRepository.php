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

    public function getGroupMessages(int $groupId): Collection
    {
        return GroupMessage::where('group_id', $groupId)
            ->orderBy('created_at', 'asc')
            ->get(['username', 'message', 'latitude', 'longitude', 'created_at']);
    }
}
