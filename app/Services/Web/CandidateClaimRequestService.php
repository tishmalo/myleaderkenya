<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Contracts\Repositories\Admin\UserRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateClaimRequestRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateRelationshipRepositoryInterface;
use App\Contracts\Repositories\Web\CampaignToolRequestRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateClaimRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CandidateClaimRequestService
{
    public function __construct(
        private CandidateClaimRequestRepositoryInterface $claimRequests,
        private CandidateRelationshipRepositoryInterface $relationships,
        private CampaignToolRequestRepositoryInterface $toolRequests,
        private UserRepositoryInterface $users,
        private CandidateRepositoryInterface $candidates
    ) {}

    public function createPublicRequest(Candidate $candidate, array $data): CandidateClaimRequest
    {
        return DB::transaction(function () use ($candidate, $data): CandidateClaimRequest {
            $duplicate = $this->claimRequests->pendingDuplicate($candidate, $data['email'], $data['relationship']);

            if ($duplicate) {
                return $duplicate;
            }

            $emailHash = hash('sha256', Str::lower(trim((string) $data['email'])));

            if ($this->users->findByEmailHash($emailHash)) {
                throw ValidationException::withMessages([
                    ($data['_email_field'] ?? 'email') => 'An account already exists for that email. Please sign in instead.',
                ]);
            }

            $user = $this->users->createUser([
                'name' => $data['name'],
                'username' => $this->uniqueUsername($data['name']),
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'user',
                'phone' => $data['phone'] ?? null,
                'relationship' => null,
                'is_aspirant' => false,
                'email_verified_at' => null,
            ]);

            return $this->claimRequests->create($candidate, [
                'user_id' => $user->id,
                'relationship' => $data['relationship'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'status' => CandidateClaimRequest::STATUS_PENDING,
            ]);
        });
    }

    public function createAuthenticatedRequest(Candidate $candidate, User $user, string $relationship): CandidateClaimRequest
    {
        return DB::transaction(function () use ($candidate, $user, $relationship): CandidateClaimRequest {
            $duplicate = $this->claimRequests->activeDuplicateForUser($candidate, $user, $relationship);

            if ($duplicate) {
                if ($relationship === 'adopter') {
                    return $duplicate;
                }

                throw ValidationException::withMessages([
                    'relationship' => 'You already have a pending or approved claim for this aspirant in that role.',
                ]);
            }

            return $this->claimRequests->create($candidate, [
                'user_id' => $user->id,
                'relationship' => $relationship,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'password' => $user->password,
                'status' => CandidateClaimRequest::STATUS_PENDING,
            ]);
        });
    }

    public function claimsForUser(User $user): Collection
    {
        return $this->claimRequests->forUser($user);
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

            if ($claimRequest->relationship !== 'adopter') {
                $this->relationships->attach($user, $candidate, $claimRequest->relationship);
            }

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

            $approved = $this->claimRequests->markApproved($claimRequest, $reviewer->id, $note);

            if ($claimRequest->relationship === 'adopter' && $user->id) {
                $this->toolRequests->updateAdoptionStatus($user->id, $candidate->id, 'in_progress');
            }

            return $approved;
        });
    }

    public function reject(CandidateClaimRequest $claimRequest, User $reviewer, ?string $note = null): CandidateClaimRequest
    {
        $rejected = $this->claimRequests->markRejected($claimRequest, $reviewer->id, $note);

        if ($claimRequest->relationship === 'adopter' && $claimRequest->user_id) {
            $this->toolRequests->updateAdoptionStatus($claimRequest->user_id, $claimRequest->candidate_id, 'cancelled');
        }

        return $rejected;
    }

    public function updateDashboardAccess(CandidateClaimRequest $claimRequest, bool $enabled): void
    {
        $claimRequest->load(['candidate', 'user']);

        if (! $claimRequest->user
            || $claimRequest->status !== CandidateClaimRequest::STATUS_APPROVED
            || $claimRequest->relationship === 'adopter') {
            return;
        }

        $this->relationships->updateDashboardAccess($claimRequest->user, $claimRequest->candidate, $enabled);
    }

    private function userForClaimRequest(CandidateClaimRequest $claimRequest): User
    {
        $user = $claimRequest->user
            ?: $this->users->findByEmailHash((string) $claimRequest->email_hash);

        if (! $user) {
            throw new \LogicException('The pending claim has no claimant account.');
        }

        // Approval activates the relationship but never resets existing credentials or PII.
        $this->users->updateUser($user, [
            'relationship' => $claimRequest->relationship === 'adopter'
                ? $user->relationship
                : $claimRequest->relationship,
            'is_aspirant' => $claimRequest->relationship === 'aspirant' || $user->is_aspirant,
            'email_verified_at' => $user->email_verified_at ?: now(),
        ]);

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
