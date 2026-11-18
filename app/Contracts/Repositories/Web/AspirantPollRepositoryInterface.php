<?php

namespace App\Contracts\Repositories\Web;

use App\Models\AspirantPoll;
use App\Models\Group;
use App\Models\GroupMessage;
use Illuminate\Support\Collection;

interface AspirantPollRepositoryInterface
{
    public function recentForCandidate(int $candidateId, int $limit): Collection;

    public function create(array $data): AspirantPoll;

    public function findGroup(string $name, int $creatorId): ?Group;

    public function createGroup(array $data): Group;

    public function inviteCodeExists(string $code): bool;

    public function addGroupMember(int $groupId, int $userId): void;

    public function createGroupMessage(array $data): GroupMessage;
}
