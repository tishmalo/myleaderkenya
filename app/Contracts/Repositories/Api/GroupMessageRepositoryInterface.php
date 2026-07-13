<?php

namespace App\Contracts\Repositories\Api;

use App\Models\GroupMessage;
use Illuminate\Support\Collection;

interface GroupMessageRepositoryInterface
{
    public function create(array $data): GroupMessage;

    public function getGroupMessages(int $groupId, ?int $userId = null): Collection;
}