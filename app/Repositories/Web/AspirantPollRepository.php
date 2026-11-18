<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\AspirantPollRepositoryInterface;
use App\Models\AspirantPoll;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use Illuminate\Support\Collection;

class AspirantPollRepository implements AspirantPollRepositoryInterface
{
    public function recentForCandidate(int $candidateId, int $limit): Collection
    {
        return AspirantPoll::with(['group', 'responses'])
            ->where('candidate_id', $candidateId)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function create(array $data): AspirantPoll
    {
        return AspirantPoll::create($data);
    }

    public function findGroup(string $name, int $creatorId): ?Group
    {
        return Group::where('name', $name)
            ->where('created_by', $creatorId)
            ->first();
    }

    public function createGroup(array $data): Group
    {
        return Group::create($data);
    }

    public function inviteCodeExists(string $code): bool
    {
        return Group::where('invite_code', $code)->exists();
    }

    public function addGroupMember(int $groupId, int $userId): void
    {
        GroupMember::firstOrCreate([
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);
    }

    public function createGroupMessage(array $data): GroupMessage
    {
        return GroupMessage::create($data);
    }
}
