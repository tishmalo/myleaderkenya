<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\GroupRepositoryInterface;
use App\Models\Group;
use Illuminate\Support\Collection;

class GroupRepository implements GroupRepositoryInterface
{
    public function create(array $data): Group
    {
        return Group::create($data);
    }

    public function findByInviteCode(string $code): ?Group
    {
        return Group::where('invite_code', strtoupper($code))->first();
    }

    public function getUserGroups(int $userId): Collection
    {
        return Group::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->select('id', 'name', 'description', 'invite_code', 'created_at')
        ->withCount('members')
        ->latest()
        ->get();
    }
}
