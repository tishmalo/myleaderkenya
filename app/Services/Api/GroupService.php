<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\GroupRepositoryInterface;
use App\Contracts\Repositories\Api\GroupMemberRepositoryInterface;
use App\Contracts\Repositories\Api\GroupMessageRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GroupService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private GroupMemberRepositoryInterface $groupMemberRepository,
        private GroupMessageRepositoryInterface $groupMessageRepository
    ) {}

    public function createGroup(User $user, array $data): array
    {
        // Generate unique 8-character invite code
        do {
            $inviteCode = strtoupper(Str::random(8));
        } while ($this->groupRepository->findByInviteCode($inviteCode));

        $group = $this->groupRepository->create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by'  => $user->id,
            'invite_code' => $inviteCode,
        ]);

        // Creator automatically becomes a member
        $this->groupMemberRepository->create([
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ]);

        return [
            'message'     => 'Group created successfully',
            'group'       => [
                'id'          => $group->id,
                'name'        => $group->name,
                'description' => $group->description,
                'invite_code' => $group->invite_code,
                'invite_link' => "yourapp://group/join?code={$group->invite_code}",
            ]
        ];
    }

    public function joinGroup(User $user, string $inviteCode): array
    {
        $group = $this->groupRepository->findByInviteCode($inviteCode);

        if (!$group) {
            throw new \Exception('Invalid or expired invite code', 404);
        }

        // Check if already a member
        if ($this->groupMemberRepository->isMember($group->id, $user->id)) {
            throw new \Exception('You are already a member of this group', 400);
        }

        $this->groupMemberRepository->create([
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ]);

        return [
            'message' => 'Successfully joined group',
            'group'   => [
                'id'          => $group->id,
                'name'        => $group->name,
                'description' => $group->description,
            ]
        ];
    }

    public function sendGroupMessage(User $user, array $data): array
    {
        // Verify user is a member of the group
        if (!$this->groupMemberRepository->isMember($data['group_id'], $user->id)) {
            throw new \Exception('You are not a member of this group', 403);
        }

        $groupMessage = $this->groupMessageRepository->create([
            'group_id'  => $data['group_id'],
            'username'  => $user->username ?? $user->name ?? 'Anonymous',
            'message'   => $data['message'],
            'latitude'  => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);

        return [
            'message' => 'Message sent to group successfully',
            'sent'    => $groupMessage->only(['id', 'username', 'message', 'created_at'])
        ];
    }

    public function getGroupMessages(User $user, int $groupId): Collection
    {
        // Verify user is a member
        if (!$this->groupMemberRepository->isMember($groupId, $user->id)) {
            throw new \Exception('You are not a member of this group', 403);
        }

        return $this->groupMessageRepository->getGroupMessages($groupId);
    }

    public function getUserGroups(User $user): Collection
    {
        return $this->groupRepository->getUserGroups($user->id);
    }
}
