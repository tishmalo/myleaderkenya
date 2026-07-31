<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Candidate;
use App\Models\CandidateTokenPackage;
use App\Models\PoliticalParty;
use App\Models\PoliticalPartyAccountRequest;
use App\Models\PoliticalPartyCandidateClaim;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PoliticalPartyManagementRepositoryInterface
{
    public function activePartyForUser(User $user): ?PoliticalParty;

    public function userBelongsToParty(User $user, PoliticalParty $party): bool;

    public function userIsPartyAdmin(User $user, PoliticalParty $party): bool;

    public function paginateCandidates(PoliticalParty $party, array $filters, int $perPage): LengthAwarePaginator;

    public function positions(): Collection;

    public function activeTokenPackages(): Collection;

    public function recentPurchases(PoliticalParty $party, int $limit): Collection;

    public function recentTransactions(PoliticalParty $party, int $limit): Collection;

    public function officials(PoliticalParty $party): Collection;

    public function recentClaims(PoliticalParty $party, int $limit): Collection;

    public function searchEligibleCandidates(PoliticalParty $party, string $query, int $limit): Collection;

    public function searchClaimableCandidates(PoliticalParty $party, string $query, int $limit): Collection;

    public function supportGroupTypes(): Collection;

    public function findParty(int $partyId): PoliticalParty;

    public function findCandidate(int $candidateId): Candidate;

    public function loadCandidateSupportContacts(Candidate $candidate): Candidate;

    public function findActiveTokenPackage(int $packageId): CandidateTokenPackage;

    public function pendingAccountRequestExists(PoliticalParty $party, string $email): bool;

    public function createAccountRequest(array $data): PoliticalPartyAccountRequest;

    public function pendingCandidateClaimExists(PoliticalParty $party, Candidate $candidate): bool;

    public function createCandidateClaim(array $data): PoliticalPartyCandidateClaim;

    public function attachOfficial(PoliticalParty $party, User $user, string $role, string $status = 'active'): void;

    public function detachOfficial(PoliticalParty $party, User $user): void;

    public function updateOfficialStatus(PoliticalParty $party, User $user, string $status): void;

    public function hasActiveOfficial(PoliticalParty $party): bool;

    public function accountRequests(int $perPage): LengthAwarePaginator;

    public function candidateClaims(int $perPage): LengthAwarePaginator;

    public function partiesForAdmin(): Collection;

    public function recentAdminPurchases(int $limit): Collection;

    public function recentAdminTransactions(int $limit): Collection;

    public function authorizationDocumentExists(
        PoliticalPartyAccountRequest $request,
    ): bool;

    public function updateAccountRequest(PoliticalPartyAccountRequest $request, array $data): bool;

    public function updateCandidateClaim(PoliticalPartyCandidateClaim $claim, array $data): bool;

    public function assignCandidateToParty(Candidate $candidate, PoliticalParty $party): bool;

    public function rejectCompetingClaims(PoliticalPartyCandidateClaim $claim, array $data): int;
}
