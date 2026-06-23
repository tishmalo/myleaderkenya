<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\GroupMemberRepositoryInterface;
use App\Models\GroupMember;

class GroupMemberRepository implements GroupMemberRepositoryInterface
{
    public function create(array $data): GroupMember
    {
        return GroupMember::create($data);
    }

    public function isMember(int $groupId, int $userId): bool
    {
        return GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->exists();
    }
}
