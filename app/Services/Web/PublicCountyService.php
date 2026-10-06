<?php

namespace App\Services\Web;

use App\Models\Candidate;
use App\Models\Constituency;
use App\Models\County;
use App\Models\Position;
use App\Support\LocationName;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PublicCountyService
{
    public function __construct(private UserLinkService $links) {}

    public function directoryData(): array
    {
        $counties = County::query()
            ->orderBy('name')
            ->get()
            ->each(function (County $county): void {
                $county->approved_aspirant_count = Candidate::query()
                    ->where('approval_status', 'approved')
                    ->whereIn('county', LocationName::variants($county->name))
                    ->count();
            });

        return ['counties' => $counties];
    }

    public function dataFor(County $county, int $perPage = 12): array
    {
        $candidateGroups = $this->positionsWithApprovedCandidates($county)
            ->map(fn (Position $position): array => [
                'position' => $position,
                'candidates' => $this->paginateApprovedCandidatesForPosition($county, $position, $perPage),
            ]);
        $candidateTotal = $candidateGroups->sum(
            fn (array $group): int => $group['candidates']->total(),
        );

        return [
            'county' => $county,
            'candidateGroups' => $candidateGroups,
            'candidateTotal' => $candidateTotal,
            'constituencies' => $this->constituenciesFor($county),
            'links' => $this->links->forCounty($county),
        ];
    }

    /**
     * The county page a candidate belongs to, if the county is one we know.
     * Aspirants keep their county as free text, so the name is matched against
     * the counties table tolerantly.
     */
    public function countyFor(Candidate $candidate): ?County
    {
        return County::query()
            ->whereIn('name', LocationName::variants($candidate->county))
            ->first();
    }

    private function positionsWithApprovedCandidates(County $county): Collection
    {
        $positionIds = Candidate::query()
            ->select('position_id')
            ->where('approval_status', 'approved')
            ->whereNotNull('position_id')
            ->whereIn('county', LocationName::variants($county->name));

        return Position::query()
            ->whereIn('id', $positionIds)
            ->ordered()
            ->get();
    }

    private function paginateApprovedCandidatesForPosition(
        County $county,
        Position $position,
        int $perPage,
    ): LengthAwarePaginator {
        return Candidate::query()
            ->with(['position:id,name', 'politicalParty:id,name,abbreviation'])
            ->where('approval_status', 'approved')
            ->where('position_id', $position->id)
            ->whereIn('county', LocationName::variants($county->name))
            ->orderByDesc('featured')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'position_'.$position->id.'_page')
            ->withQueryString();
    }

    private function constituenciesFor(County $county): Collection
    {
        return Constituency::query()
            ->with('county:id,name')
            ->where('county_id', $county->id)
            ->orderBy('name')
            ->get()
            ->each(function (Constituency $constituency): void {
                $countyVariants = LocationName::variants($constituency->county?->name);

                $constituency->approved_aspirant_count = Candidate::query()
                    ->where('approval_status', 'approved')
                    ->whereIn('constituency', LocationName::variants($constituency->name))
                    ->whereIn('county', $countyVariants)
                    ->count();
            });
    }
}
