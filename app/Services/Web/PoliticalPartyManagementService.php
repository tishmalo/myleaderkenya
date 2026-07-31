<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\UserRepositoryInterface;
use App\Contracts\Repositories\Web\PoliticalPartyManagementRepositoryInterface;
use App\Models\Candidate;
use App\Models\PoliticalParty;
use App\Models\PoliticalPartyAccountRequest;
use App\Models\PoliticalPartyCandidateClaim;
use App\Models\User;
use App\Services\Admin\CandidateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PoliticalPartyManagementService
{
    public function __construct(
        private PoliticalPartyManagementRepositoryInterface $parties,
        private UserRepositoryInterface $users,
        private CandidateService $candidates,
        private PoliticalPartyTokenService $tokens,
    ) {}

    public function partyForUser(User $user): PoliticalParty
    {
        return $this->parties->activePartyForUser($user) ?? abort(403);
    }

    public function dashboardData(User $user, array $filters): array
    {
        $party = $this->partyForUser($user);

        return [
            'party' => $party,
            'membership' => $party->pivot,
            'candidates' => $this->parties->paginateCandidates($party, $filters, 20),
            'positions' => $this->parties->positions(),
            'wallet' => $this->tokens->wallet($party),
            'packages' => $this->parties->activeTokenPackages(),
            'purchases' => $this->parties->recentPurchases($party, 10),
            'transactions' => $this->parties->recentTransactions($party, 20),
            'officials' => $this->parties->officials($party),
            'claims' => $this->parties->recentClaims($party, 10),
            'eligibleCandidates' => $this->parties->eligibleCandidates($party),
            'claimableCandidates' => $this->parties->claimableCandidates($party, 100),
        ];
    }

    public function candidateFormData(User $user, Candidate $candidate): array
    {
        $party = $this->partyForUser($user);

        if ($candidate->exists) {
            $this->ensureCandidateBelongsToParty($candidate, $party);
            $candidate = $this->parties->loadCandidateSupportContacts($candidate);
        }

        return [
            'party' => $party,
            'candidate' => $candidate,
            'positions' => $this->parties->positions(),
            'supportGroupTypes' => $this->parties->supportGroupTypes(),
        ];
    }

    public function createCandidate(User $user, array $data, array $files): Candidate
    {
        $party = $this->partyForUser($user);
        $data['political_party_id'] = $party->id;
        $data['approval_status'] = 'pending';

        return $this->candidates->createCandidate(
            $data,
            $files['profile_picture'] ?? null,
            $files['cover_photo'] ?? null,
            $files['campaign_poster'] ?? null,
            null,
            $files['campaign_skiza_audio'] ?? null,
        );
    }

    public function updateCandidate(
        User $user,
        Candidate $candidate,
        array $data,
        array $files,
    ): void {
        $party = $this->partyForUser($user);
        $this->ensureCandidateBelongsToParty($candidate, $party);

        unset(
            $data['political_party_id'],
            $data['approval_status'],
            $data['featured'],
            $data['user_id'],
        );

        $this->candidates->updateCandidate(
            $candidate,
            $data,
            $files['profile_picture'] ?? null,
            $files['cover_photo'] ?? null,
            $files['campaign_poster'] ?? null,
            null,
            $files['campaign_skiza_audio'] ?? null,
        );
    }

    public function requestAccess(
        PoliticalParty $party,
        array $data,
        UploadedFile $authorizationDocument,
    ): PoliticalPartyAccountRequest {
        $normalizedEmail = strtolower(trim($data['email']));

        if ($this->parties->pendingAccountRequestExists($party, $normalizedEmail)) {
            throw ValidationException::withMessages([
                'email' => 'A pending request already exists for this email.',
            ]);
        }

        return DB::transaction(function () use (
            $party,
            $data,
            $normalizedEmail,
            $authorizationDocument,
        ): PoliticalPartyAccountRequest {
            $user = $this->users->findByEmailHash(hash('sha256', $normalizedEmail));

            if (! $user) {
                $user = $this->users->createUser([
                    'name' => $data['name'],
                    'email' => $normalizedEmail,
                    'phone' => $data['phone'],
                    'password' => $data['password'],
                    'role' => 'user',
                ]);
            }

            return $this->parties->createAccountRequest([
                'political_party_id' => $party->id,
                'user_id' => $user->id,
                'name' => $data['name'],
                'email' => $normalizedEmail,
                'phone' => $data['phone'],
                'party_title' => $data['party_title'],
                'authorization_document' => $authorizationDocument
                    ->store('party-authorizations'),
            ]);
        });
    }

    public function createClaim(User $user, int $candidateId): PoliticalPartyCandidateClaim
    {
        $party = $this->partyForUser($user);
        $candidate = $this->parties->findCandidate($candidateId);

        if ($candidate->political_party_id === $party->id) {
            throw ValidationException::withMessages([
                'candidate_id' => 'This aspirant already belongs to your party.',
            ]);
        }

        if ($this->parties->pendingCandidateClaimExists($party, $candidate)) {
            throw ValidationException::withMessages([
                'candidate_id' => 'A pending claim already exists.',
            ]);
        }

        return $this->parties->createCandidateClaim([
            'political_party_id' => $party->id,
            'candidate_id' => $candidate->id,
            'requested_by' => $user->id,
        ]);
    }

    public function saveOfficial(User $actor, PoliticalParty $party, array $data): User
    {
        if ($this->parties->userBelongsToParty($actor, $party)) {
            $this->ensurePartyAdmin($actor, $party);
        }

        $emailHash = hash('sha256', strtolower(trim($data['email'])));
        $official = $this->users->findByEmailHash($emailHash);

        if (! $official) {
            $official = $this->users->createUser([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'user',
            ]);
        }

        $this->parties->attachOfficial($party, $official, $data['role']);

        return $official;
    }

    public function saveAdminOfficial(User $actor, array $data): User
    {
        $party = $this->parties->findParty((int) $data['political_party_id']);

        return $this->saveOfficial($actor, $party, $data);
    }

    public function removeOfficial(User $actor, User $official): void
    {
        $party = $this->partyForUser($actor);
        $this->ensurePartyAdmin($actor, $party);

        if ($actor->is($official)) {
            throw ValidationException::withMessages([
                'official' => 'You cannot remove your own access.',
            ]);
        }

        $this->parties->detachOfficial($party, $official);
    }

    public function purchaseTokens(User $user, array $data): string
    {
        $party = $this->partyForUser($user);
        $package = $this->parties->findActiveTokenPackage((int) $data['package_id']);

        return $this->tokens->purchase($party, $user, $package, $data);
    }

    public function distributeTokens(User $user, array $data): void
    {
        $party = $this->partyForUser($user);
        $candidate = $this->parties->findCandidate((int) $data['candidate_id']);

        $this->tokens->transfer($party, $candidate, $user, (int) $data['amount']);
    }

    public function adminData(): array
    {
        return [
            'accountRequests' => $this->parties->accountRequests(20),
            'candidateClaims' => $this->parties->candidateClaims(20),
            'parties' => $this->parties->partiesForAdmin(),
            'partyPurchases' => $this->parties->recentAdminPurchases(25),
            'partyTransactions' => $this->parties->recentAdminTransactions(50),
        ];
    }

    public function updateOfficialStatus(
        PoliticalParty $party,
        User $official,
        string $status,
    ): void {
        $this->parties->updateOfficialStatus($party, $official, $status);
    }

    public function authorizationDocumentPath(
        PoliticalPartyAccountRequest $request,
    ): string {
        abort_unless($this->parties->authorizationDocumentExists($request), 404);

        return $request->authorization_document;
    }

    public function reviewAccountRequest(
        PoliticalPartyAccountRequest $request,
        string $status,
        ?string $notes,
        User $reviewer,
    ): void {
        DB::transaction(function () use ($request, $status, $notes, $reviewer): void {
            $this->parties->updateAccountRequest($request, [
                'status' => $status,
                'review_notes' => $notes,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            if ($status !== 'approved' || ! $request->user_id) {
                return;
            }

            $role = $this->parties->hasActiveOfficial($request->politicalParty)
                ? 'party_staff'
                : 'party_admin';

            $this->parties->attachOfficial(
                $request->politicalParty,
                $request->user,
                $role,
            );
        });
    }

    public function reviewCandidateClaim(
        PoliticalPartyCandidateClaim $claim,
        string $status,
        ?string $notes,
        User $reviewer,
    ): void {
        DB::transaction(function () use ($claim, $status, $notes, $reviewer): void {
            $reviewData = [
                'status' => $status,
                'review_notes' => $notes,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ];
            $this->parties->updateCandidateClaim($claim, $reviewData);

            if ($status !== 'approved') {
                return;
            }

            $this->parties->assignCandidateToParty(
                $claim->candidate,
                $claim->politicalParty,
            );
            $this->parties->rejectCompetingClaims($claim, [
                'status' => 'rejected',
                'review_notes' => 'Superseded by approved party affiliation claim.',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);
        });
    }

    private function ensureCandidateBelongsToParty(
        Candidate $candidate,
        PoliticalParty $party,
    ): void {
        abort_unless($candidate->political_party_id === $party->id, 403);
    }

    private function ensurePartyAdmin(User $user, PoliticalParty $party): void
    {
        $isAdmin = $this->parties->userIsPartyAdmin($user, $party);

        abort_unless($isAdmin, 403);
    }
}
