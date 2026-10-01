<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\ResourceLinkRepositoryInterface;
use App\Models\Candidate;
use App\Models\Constituency;
use App\Models\County;
use App\Models\PoliticalParty;
use App\Models\ResourceLink;
use App\Models\Ward;
use App\Support\LocationName;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ResourceLinkRepository implements ResourceLinkRepositoryInterface
{
    public function paginateForCreator(int $userId, int $perPage = 12): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->where('user_id', $userId)
            ->orderByRaw("CASE approval_status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): ResourceLink
    {
        return ResourceLink::create($data);
    }

    public function findCandidateById(int $candidateId): ?Candidate
    {
        return Candidate::query()
            ->select([
                'id', 'name', 'nick_name', 'profile_picture',
                'position_id', 'political_party_id', 'country', 'county',
                'constituency', 'ward',
            ])
            ->with(['position:id,name', 'politicalParty:id,name'])
            ->where('approval_status', 'approved')
            ->find($candidateId);
    }

    public function allCounties(): Collection
    {
        return County::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function allConstituencies(?int $countyId = null): Collection
    {
        return Constituency::query()
            ->when($countyId, fn ($query) => $query->where('county_id', $countyId))
            ->orderBy('name')
            ->get(['id', 'name', 'county_id']);
    }

    public function allWards(?int $constituencyId = null): Collection
    {
        return Ward::query()
            ->when($constituencyId, fn ($query) => $query->where('constituency_id', $constituencyId))
            ->orderBy('name')
            ->get(['id', 'name', 'constituency_id']);
    }

    public function allPoliticalParties(): Collection
    {
        return PoliticalParty::query()
            ->published()
            ->ordered()
            ->get(['id', 'name', 'abbreviation']);
    }

    public function approvedForCandidate(Candidate $candidate): Collection
    {
        $query = $this->approvedQuery()
            ->with(['county:id,name', 'constituency:id,name', 'ward:id,name', 'politicalParty:id,name', 'candidate:id,name'])
            ->where(function (Builder $match) use ($candidate): void {
                $match->where('candidate_id', $candidate->id);

                if ($candidate->political_party_id) {
                    $match->orWhere('political_party_id', $candidate->political_party_id);
                }

                $this->orMatchLocation($match, $candidate->county, 'county');
                $this->orMatchLocation($match, $candidate->constituency, 'constituency');
                $this->orMatchLocation($match, $candidate->ward, 'ward');
            });

        return $query->latest()->get();
    }

    public function approvedForCounty(County $county, int $limit = 50): Collection
    {
        return $this->approvedQuery()
            ->with(['constituency:id,name', 'ward:id,name', 'politicalParty:id,name', 'candidate:id,name'])
            ->where('county_id', $county->id)
            ->orderByDesc('followers')
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function baseQuery(): Builder
    {
        return ResourceLink::query()->with([
            'county:id,name',
            'constituency:id,name',
            'ward:id,name',
            'politicalParty:id,name',
            'candidate:id,name',
            'creator:id,name',
        ]);
    }

    /**
     * Aspirants keep their location as free-text names, so a link is matched by
     * the region name the submitter picked rather than by the region row itself.
     * Matching tolerates "Nairobi" / "Nairobi County" spelling differences.
     */
    private function orMatchLocation(Builder $query, ?string $name, string $relation): void
    {
        $variants = LocationName::variants($name);

        if ($variants !== []) {
            $query->orWhereHas($relation, fn (Builder $region) => $region->whereIn('name', $variants));
        }
    }

    private function approvedQuery(): Builder
    {
        return ResourceLink::query()->where('approval_status', ResourceLink::STATUS_APPROVED);
    }
}
