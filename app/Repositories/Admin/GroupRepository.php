<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\GroupRepositoryInterface;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use Illuminate\Database\Eloquent\Collection;

class GroupRepository implements GroupRepositoryInterface
{
    public function createGroup(array $data): Group
    {
        return Group::create($data);
    }

    public function findByInviteCode(string $code): bool
    {
        return Group::where('invite_code', $code)->exists();
    }

    public function addMember(int $groupId, int $userId): void
    {
        GroupMember::create([
            'group_id' => $groupId,
            'user_id'  => $userId,
        ]);
    }

    public function isMember(int $groupId, int $userId): bool
    {
        return GroupMember::where('group_id', $groupId)
                          ->where('user_id', $userId)
                          ->exists();
    }

    public function getGroupMessages(int $groupId): Collection
    {
        return GroupMessage::where('group_id', $groupId)
                           ->orderBy('created_at', 'asc')
                           ->get();
    }

    public function createMessage(array $data): void
    {
        GroupMessage::create($data);
    }
}
