<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\CandidateSmsBalanceRequestRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateSmsBalanceRequest;
use App\Models\User;

class SmsBalanceRequestService
{
    public function __construct(private CandidateSmsBalanceRequestRepositoryInterface $requests) {}

    public function createForAspirant(Candidate $candidate, User $user, array $data): CandidateSmsBalanceRequest
    {
        return $this->requests->create([
            'candidate_id' => $candidate->id,
            'user_id' => $user->id,
            'provider' => 'infobip',
            'requested_amount' => $data['requested_amount'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'new',
        ]);
    }

    public function updateFromAdmin(CandidateSmsBalanceRequest $request, array $data): bool
    {
        if (($data['status'] ?? $request->status) === 'followed_up' && ! $request->followed_up_at) {
            $data['followed_up_at'] = now();
        }

        return $this->requests->update($request, $data);
    }

    public function paginate(array $filters = [])
    {
        return $this->requests->paginate($filters);
    }
}
