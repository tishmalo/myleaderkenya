<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\GroupRepositoryInterface;
use App\Models\Group;
use Illuminate\Support\Str;

class GroupService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository
    ) {}

    public function createGroup(array $data, int $creatorId): Group
    {
        do {
            $inviteCode = strtoupper(Str::random(8));
        } while ($this->groupRepository->findByInviteCode($inviteCode));

        $group = $this->groupRepository->createGroup([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by'  => $creatorId,
            'invite_code' => $inviteCode,
        ]);

        $this->groupRepository->addMember($group->id, $creatorId);

        return $group;
    }

    public function getGroupIfMember(Group $group, int $userId): ?array
    {
        if (!$this->groupRepository->isMember($group->id, $userId)) {
            return null;
        }

        return [
            'group'    => $group,
            'messages' => $this->groupRepository->getGroupMessages($group->id),
        ];
    }

    public function sendMessage(Group $group, string $message, $user): bool
    {
        if (!$this->groupRepository->isMember($group->id, $user->id)) {
            return false;
        }

        $this->groupRepository->createMessage([
            'group_id' => $group->id,
            'username' => $user->username ?? $user->name ?? 'Web User',
            'message'  => $message,
        ]);

        return true;
    }
}
