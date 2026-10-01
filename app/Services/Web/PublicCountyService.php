<?php

namespace App\Services\Web;

use App\Models\Candidate;
use App\Models\Constituency;
use App\Models\County;
use App\Support\LocationName;
use Illuminate\Support\Collection;

class PublicCountyService
{
    public function __construct(private UserLinkService $links) {}

    public function dataFor(County $county): array
    {
        return [
            'county' => $county,
            'aspirants' => $this->aspirantsFor($county),
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

    private function aspirantsFor(County $county, int $limit = 24): Collection
    {
        return Candidate::query()
            ->with(['position:id,name', 'politicalParty:id,name,abbreviation'])
            ->where('approval_status', 'approved')
            ->whereIn('county', LocationName::variants($county->name))
            ->orderByDesc('featured')
            ->orderBy('name')
            ->limit($limit)
            ->get();
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
