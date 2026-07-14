<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\GroupRepositoryInterface;
use App\Contracts\Repositories\Api\GroupMemberRepositoryInterface;
use App\Contracts\Repositories\Api\GroupMessageRepositoryInterface;
use App\Models\AspirantPoll;
use App\Models\AspirantPollResponse;
use App\Models\User;
use App\Support\AspirantPollPresenter;
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
        do {
            $inviteCode = strtoupper(Str::random(8));
        } while ($this->groupRepository->findByInviteCode($inviteCode));

        $group = $this->groupRepository->create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by'  => $user->id,
            'invite_code' => $inviteCode,
        ]);

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
        if (!$this->groupMemberRepository->isMember($data['group_id'], $user->id)) {
            throw new \Exception('You are not a member of this group', 403);
        }

        $groupMessage = $this->groupMessageRepository->create([
            'group_id'  => $data['group_id'],
            'username'  => $user->username ?? $user->name ?? 'Anonymous',
            'message'   => $data['message'],
            'message_type' => 'text',
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
        if (!$this->groupMemberRepository->isMember($groupId, $user->id)) {
            throw new \Exception('You are not a member of this group', 403);
        }

        return $this->groupMessageRepository->getGroupMessages($groupId, $user->id);
    }

    public function respondToPoll(User $user, AspirantPoll $poll, int $optionIndex): array
    {
        if (! $poll->group_id || ! $this->groupMemberRepository->isMember($poll->group_id, $user->id)) {
            throw new \Exception('You are not a member of this poll group', 403);
        }

        if ($poll->scope_column && $poll->scope_value && ($user->{$poll->scope_column} ?? null) !== $poll->scope_value) {
            throw new \Exception('This poll is outside your voting bloc', 403);
        }

        $options = $poll->options ?? [];

        if (! array_key_exists($optionIndex, $options)) {
            throw new \Exception('Invalid poll option', 422);
        }

        AspirantPollResponse::updateOrCreate(
            [
                'aspirant_poll_id' => $poll->id,
                'user_id' => $user->id,
            ],
            ['option_index' => $optionIndex]
        );

        $poll->load('responses');
        $totalResponses = $poll->responses->count();

        return [
            'message' => 'Poll response recorded',
            'poll' => AspirantPollPresenter::format($poll, $user->id),
        ];
    }

    public function getUserGroups(User $user): Collection
    {
        return $this->groupRepository->getUserGroups($user->id);
    }
}
