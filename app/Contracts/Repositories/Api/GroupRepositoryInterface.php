<?php

namespace App\Contracts\Repositories\Api;

use App\Models\Group;
use Illuminate\Support\Collection;

interface GroupRepositoryInterface
{
    public function create(array $data): Group;

    public function findByInviteCode(string $code): ?Group;

    public function getUserGroups(int $userId): Collection;
}
