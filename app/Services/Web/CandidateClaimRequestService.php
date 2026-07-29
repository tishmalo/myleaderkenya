<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Contracts\Repositories\Admin\UserRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateClaimRequestRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateRelationshipRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateClaimRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CandidateClaimRequestService
{
    public function __construct(
        private CandidateClaimRequestRepositoryInterface $claimRequests,
        private CandidateRelationshipRepositoryInterface $relationships,
        private UserRepositoryInterface $users,
        private CandidateRepositoryInterface $candidates
    ) {}

    public function createPublicRequest(Candidate $candidate, array $data): CandidateClaimRequest
    {
        $duplicate = $this->claimRequests->pendingDuplicate($candidate, $data['email'], $data['relationship']);

        if ($duplicate) {
            return $duplicate;
        }

        return $this->claimRequests->create($candidate, [
            'relationship' => $data['relationship'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'status' => CandidateClaimRequest::STATUS_PENDING,
        ]);
    }

    public function listForCandidate(Candidate $candidate): Collection
    {
        return $this->claimRequests->forCandidate($candidate);
    }

    public function countsForCandidate(Candidate $candidate): array
    {
        return $this->claimRequests->countsForCandidate($candidate);
    }

    public function approve(CandidateClaimRequest $claimRequest, User $reviewer, ?string $note = null): CandidateClaimRequest
    {
        return DB::transaction(function () use ($claimRequest, $reviewer, $note): CandidateClaimRequest {
            $claimRequest->load('candidate');

            $user = $this->userForClaimRequest($claimRequest);
            $candidate = $claimRequest->candidate;

            $this->relationships->attach($user, $candidate, $claimRequest->relationship);

            if ($claimRequest->relationship === 'aspirant' && ! $candidate->user_id) {
                $this->candidates->update($candidate, [
                    'user_id' => $user->id,
                    'claimed_at' => $candidate->claimed_at ?: now(),
                    'claim_token_hash' => null,
                    'claim_token_expires_at' => null,
                    'claim_sent_at' => null,
                ]);
            }

            $claimRequest->forceFill(['user_id' => $user->id])->save();

            return $this->claimRequests->markApproved($claimRequest, $reviewer->id, $note);
        });
    }

    public function reject(CandidateClaimRequest $claimRequest, User $reviewer, ?string $note = null): CandidateClaimRequest
    {
        return $this->claimRequests->markRejected($claimRequest, $reviewer->id, $note);
    }

    private function userForClaimRequest(CandidateClaimRequest $claimRequest): User
    {
        $user = $this->users->findByEmailHash((string) $claimRequest->email_hash);

        $data = [
            'name' => $claimRequest->name,
            'email' => $claimRequest->email,
            'password' => $claimRequest->password,
            'role' => 'user',
            'phone' => $claimRequest->phone,
            'relationship' => $claimRequest->relationship,
            'is_aspirant' => $claimRequest->relationship === 'aspirant',
            'email_verified_at' => now(),
        ];

        if (! $user) {
            return $this->users->createUser(array_merge($data, [
                'username' => $this->uniqueUsername($claimRequest->name),
            ]));
        }

        $this->users->updateUser($user, $data);

        return $user->refresh();
    }

    private function uniqueUsername(string $name): string
    {
        $base = Str::limit(Str::slug($name, '_'), 40, '');

        if ($base === '') {
            $base = 'claimant';
        }

        $username = $base;
        $suffix = 1;

        while ($this->users->usernameExists($username)) {
            $username = $base . '_' . $suffix++;
        }

        return $username;
    }
}
