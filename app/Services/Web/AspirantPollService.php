<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\AspirantPollRepositoryInterface;
use App\Models\AspirantPoll;
use App\Models\Candidate;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AspirantPollService
{
    public function __construct(
        private AspirantPollRepositoryInterface $polls,
        private AspirantWorkspaceService $workspace,
        private CampaignToolCommerceService $commerce,
    ) {}

    /**
     * @param  array<int, string>  $options
     * @param  array<string, mixed>  $scope
     */
    public function store(Candidate $candidate, User $user, string $question, array $options, string $status, array $scope): AspirantPoll
    {
        $poll = DB::transaction(function () use ($candidate, $user, $question, $options, $status, $scope): AspirantPoll {
            $group = $status === 'published' ? $this->publishGroup($user, $scope) : null;

            $poll = $this->polls->create([
                'candidate_id' => $candidate->id,
                'user_id' => $user->id,
                'group_id' => $group?->id,
                'question' => $question,
                'options' => $options,
                'scope_type' => $scope['type'],
                'scope_column' => $scope['column'],
                'scope_value' => $scope['value'],
                'status' => $status,
                'published_at' => $status === 'published' ? now() : null,
            ]);

            if ($group) {
                $this->polls->createGroupMessage([
                    'group_id' => $group->id,
                    'username' => $user->username ?? $user->name ?? 'Aspirant',
                    'message' => $this->pollMessage($poll),
                    'message_type' => 'poll',
                    'aspirant_poll_id' => $poll->id,
                    'latitude' => null,
                    'longitude' => null,
                ]);
            }

            return $poll;
        });

        $this->commerce->consumeEntitlement($candidate->id, 'opinion-polls');

        return $poll;
    }

    public function resultMessage(AspirantPoll $poll, string $scopeLabel): string
    {
        return $poll->status === 'published'
            ? 'Poll published to the ' . $scopeLabel . ' chat group.'
            : 'Poll draft saved. Publish it when you are ready to send it to voters.';
    }

    /**
     * @param  array<string, mixed>  $scope
     */
    private function publishGroup(User $user, array $scope): Group
    {
        $name = $scope['label'] . ' Opinion Polls';
        $group = $this->polls->findGroup($name, $user->id) ?? $this->polls->createGroup([
            'name' => $name,
            'description' => 'Opinion polls for voters in ' . $scope['label'] . '.',
            'created_by' => $user->id,
            'invite_code' => $this->uniqueInviteCode(),
        ]);

        $this->polls->addGroupMember($group->id, $user->id);

        $this->workspace->registeredVotersQuery($scope)
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($voters) use ($group): void {
                foreach ($voters as $voter) {
                    $this->polls->addGroupMember($group->id, $voter->id);
                }
            });

        return $group;
    }

    private function uniqueInviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while ($this->polls->inviteCodeExists($code));

        return $code;
    }

    private function pollMessage(AspirantPoll $poll): string
    {
        $options = collect($poll->options)
            ->map(fn (string $option, int $index): string => ($index + 1) . '. ' . $option)
            ->implode("\n");

        return "[POLL #{$poll->id}]\n{$poll->question}\n{$options}";
    }
}
