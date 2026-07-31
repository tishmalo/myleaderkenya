<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\PoliticalPartyManagementRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateTokenPackage;
use App\Models\PoliticalParty;
use App\Models\PoliticalPartyAccountRequest;
use App\Models\PoliticalPartyCandidateClaim;
use App\Models\PoliticalPartyTokenPurchase;
use App\Models\PoliticalPartyTokenTransaction;
use App\Models\Position;
use App\Models\SupportGroupType;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PoliticalPartyManagementRepository implements PoliticalPartyManagementRepositoryInterface
{
    public function activePartyForUser(User $user): ?PoliticalParty
    {
        return $user->politicalParties()
            ->wherePivot('status', 'active')
            ->first();
    }

    public function userBelongsToParty(User $user, PoliticalParty $party): bool
    {
        return $user->politicalParties()
            ->whereKey($party->id)
            ->wherePivot('status', 'active')
            ->exists();
    }

    public function userIsPartyAdmin(User $user, PoliticalParty $party): bool
    {
        return $user->politicalParties()
            ->whereKey($party->id)
            ->wherePivot('status', 'active')
            ->wherePivot('role', 'party_admin')
            ->exists();
    }

    public function paginateCandidates(
        PoliticalParty $party,
        array $filters,
        int $perPage,
    ): LengthAwarePaginator {
        return Candidate::with(['position', 'tokenWallet'])
            ->where('political_party_id', $party->id)
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($candidateQuery) use ($search): void {
                    $candidateQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nick_name', 'like', "%{$search}%");
                });
            })
            ->when(
                $filters['position'] ?? null,
                fn ($query, $position) => $query->where('position_id', $position)
            )
            ->when(
                $filters['approval_status'] ?? null,
                fn ($query, $status) => $query->where('approval_status', $status)
            )
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function positions(): Collection
    {
        return Position::ordered()->get();
    }

    public function activeTokenPackages(): Collection
    {
        return CandidateTokenPackage::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function recentPurchases(PoliticalParty $party, int $limit): Collection
    {
        return PoliticalPartyTokenPurchase::where('political_party_id', $party->id)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function recentTransactions(PoliticalParty $party, int $limit): Collection
    {
        return PoliticalPartyTokenTransaction::with('candidate')
            ->where('political_party_id', $party->id)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function officials(PoliticalParty $party): Collection
    {
        return $party->officials()->get();
    }

    public function recentClaims(PoliticalParty $party, int $limit): Collection
    {
        return PoliticalPartyCandidateClaim::with('candidate')
            ->where('political_party_id', $party->id)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function eligibleCandidates(PoliticalParty $party): Collection
    {
        return Candidate::where('political_party_id', $party->id)
            ->where('approval_status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function claimableCandidates(PoliticalParty $party, int $limit): Collection
    {
        return Candidate::with('politicalParty')
            ->where(function ($query) use ($party): void {
                $query
                    ->whereNull('political_party_id')
                    ->orWhere('political_party_id', '!=', $party->id);
            })
            ->orderBy('name')
            ->take($limit)
            ->get();
    }

    public function supportGroupTypes(): Collection
    {
        return SupportGroupType::active()->ordered()->get();
    }

    public function findParty(int $partyId): PoliticalParty
    {
        return PoliticalParty::findOrFail($partyId);
    }

    public function findCandidate(int $candidateId): Candidate
    {
        return Candidate::findOrFail($candidateId);
    }

    public function loadCandidateSupportContacts(Candidate $candidate): Candidate
    {
        return $candidate->load('supportContacts');
    }

    public function findActiveTokenPackage(int $packageId): CandidateTokenPackage
    {
        return CandidateTokenPackage::whereKey($packageId)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function pendingAccountRequestExists(
        PoliticalParty $party,
        string $email,
    ): bool {
        return PoliticalPartyAccountRequest::where('political_party_id', $party->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->exists();
    }

    public function createAccountRequest(array $data): PoliticalPartyAccountRequest
    {
        return PoliticalPartyAccountRequest::create($data);
    }

    public function pendingCandidateClaimExists(
        PoliticalParty $party,
        Candidate $candidate,
    ): bool {
        return PoliticalPartyCandidateClaim::where('political_party_id', $party->id)
            ->where('candidate_id', $candidate->id)
            ->where('status', 'pending')
            ->exists();
    }

    public function createCandidateClaim(array $data): PoliticalPartyCandidateClaim
    {
        return PoliticalPartyCandidateClaim::create($data);
    }

    public function attachOfficial(
        PoliticalParty $party,
        User $user,
        string $role,
        string $status = 'active',
    ): void {
        $party->officials()->syncWithoutDetaching([
            $user->id => [
                'role' => $role,
                'status' => $status,
            ],
        ]);
    }

    public function detachOfficial(PoliticalParty $party, User $user): void
    {
        $party->officials()->detach($user->id);
    }

    public function updateOfficialStatus(
        PoliticalParty $party,
        User $user,
        string $status,
    ): void {
        $party->officials()->updateExistingPivot(
            $user->id,
            ['status' => $status],
        );
    }

    public function hasActiveOfficial(PoliticalParty $party): bool
    {
        return $party->officials()
            ->wherePivot('status', 'active')
            ->exists();
    }

    public function accountRequests(int $perPage): LengthAwarePaginator
    {
        return PoliticalPartyAccountRequest::with(['politicalParty', 'user'])
            ->latest()
            ->paginate($perPage, ['*'], 'accounts');
    }

    public function candidateClaims(int $perPage): LengthAwarePaginator
    {
        return PoliticalPartyCandidateClaim::with([
            'politicalParty',
            'candidate.politicalParty',
            'requester',
        ])->latest()->paginate($perPage, ['*'], 'claims');
    }

    public function partiesForAdmin(): Collection
    {
        return PoliticalParty::with(['officials', 'tokenWallet'])
            ->ordered()
            ->get();
    }

    public function recentAdminPurchases(int $limit): Collection
    {
        return PoliticalPartyTokenPurchase::with('politicalParty')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function recentAdminTransactions(int $limit): Collection
    {
        return PoliticalPartyTokenTransaction::with('candidate')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function authorizationDocumentExists(
        PoliticalPartyAccountRequest $request,
    ): bool {
        return Storage::exists($request->authorization_document);
    }

    public function updateAccountRequest(
        PoliticalPartyAccountRequest $request,
        array $data,
    ): bool {
        return $request->update($data);
    }

    public function updateCandidateClaim(
        PoliticalPartyCandidateClaim $claim,
        array $data,
    ): bool {
        return $claim->update($data);
    }

    public function assignCandidateToParty(
        Candidate $candidate,
        PoliticalParty $party,
    ): bool {
        return $candidate->update(['political_party_id' => $party->id]);
    }

    public function rejectCompetingClaims(
        PoliticalPartyCandidateClaim $claim,
        array $data,
    ): int {
        return PoliticalPartyCandidateClaim::where('candidate_id', $claim->candidate_id)
            ->whereKeyNot($claim->id)
            ->where('status', 'pending')
            ->update($data);
    }
}
