<?php

namespace App\Contracts\Repositories\Api;

use App\Models\GroupMember;

interface GroupMemberRepositoryInterface
{
    public function create(array $data): GroupMember;

    public function isMember(int $groupId, int $userId): bool;
}
