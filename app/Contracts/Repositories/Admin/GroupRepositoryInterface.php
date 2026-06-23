<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Group;
use Illuminate\Database\Eloquent\Collection;

interface GroupRepositoryInterface
{
    public function createGroup(array $data): Group;
    public function findByInviteCode(string $code): bool;
    public function addMember(int $groupId, int $userId): void;
    public function isMember(int $groupId, int $userId): bool;
    public function getGroupMessages(int $groupId): Collection;
    public function createMessage(array $data): void;
}
