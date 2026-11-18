<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CandidateCallCenterRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateCallLog;
use App\Models\CandidateCallScript;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CandidateCallCenterService
{
    public function __construct(
        private CandidateCallCenterRepositoryInterface $callCenter,
        private AspirantWorkspaceService $workspace,
        private CampaignToolCommerceService $commerce,
    ) {}

    /**
     * @param  array<string, mixed>  $scope
     */
    public function saveScript(Candidate $candidate, User $user, string $script, string $callbackPriority, array $scope): CandidateCallScript
    {
        $saved = $this->callCenter->saveScript($candidate->id, [
            'user_id' => $user->id,
            'script' => $script,
            'callback_priority' => $callbackPriority,
            'scope_type' => $scope['type'],
            'scope_column' => $scope['column'],
            'scope_value' => $scope['value'],
        ]);

        $this->commerce->consumeEntitlement($candidate->id, 'call-center');

        return $saved;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $scope
     */
    public function recordCall(Candidate $candidate, User $user, array $validated, array $scope): ?CandidateCallLog
    {
        $voter = $this->workspace->registeredVotersQuery($scope)
            ->whereKey($validated['voter_user_id'])
            ->whereNotNull('phone')
            ->first();

        if (! $voter) {
            return null;
        }

        $callLog = DB::transaction(fn (): CandidateCallLog => $this->callCenter->createLog([
            'candidate_id' => $candidate->id,
            'user_id' => $user->id,
            'voter_user_id' => $voter->id,
            'voter_name' => $voter->name ?: $voter->username,
            'voter_phone' => $voter->phone,
            'outcome' => $validated['outcome'],
            'notes' => $validated['notes'] ?? null,
            'callback_at' => $validated['callback_at'] ?? null,
            'scope_type' => $scope['type'],
            'scope_column' => $scope['column'],
            'scope_value' => $scope['value'],
            'called_at' => now(),
        ]));

        $this->commerce->consumeEntitlement($candidate->id, 'call-center');

        return $callLog;
    }
}
