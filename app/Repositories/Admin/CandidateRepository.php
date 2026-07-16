<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Models\PoliticalParty;
use App\Models\Candidate;
use App\Models\Constituency;
use App\Models\County;
use App\Models\NewsArticle;
use App\Models\Position;
use App\Models\Ward;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CandidateRepository implements CandidateRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Candidate::with(['position', 'politicalParty']);

        if (!empty($filters['candidate'])) {
            $candidate = $filters['candidate'];
            $query->where(function ($query) use ($candidate) {
                $query->where('name', 'like', "%{$candidate}%")
                    ->orWhere('nick_name', 'like', "%{$candidate}%");
            });
        }

        if (!empty($filters['position'])) {
            $query->where('position_id', $filters['position']);
        }

        if (!empty($filters['political_party'])) {
            $query->where('political_party_id', $filters['political_party']);
        }

        if (!empty($filters['approval_status']) && Schema::hasColumn('candidates', 'approval_status')) {
            $query->where('approval_status', $filters['approval_status']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Candidate
    {
        return Candidate::create($data);
    }

    public function update(Candidate $candidate, array $data): bool
    {
        return $candidate->update($data);
    }

    public function delete(Candidate $candidate): bool
    {
        return $candidate->delete();
    }

    public function allPositions(): Collection
    {
        return Position::ordered()->get();
    }

    public function allPoliticalParties(): Collection
    {
        return PoliticalParty::published()->ordered()->get();
    }

    public function allCounties(): Collection
    {
        return County::orderBy('name')->pluck('name');
    }

    public function allCountries(): Collection
    {
        $query = Candidate::whereNotNull('country')
            ->where('country', '!=', '');

        if (Schema::hasColumn('candidates', 'approval_status')) {
            $query->where('approval_status', 'approved');
        }

        return $query
            ->distinct()
            ->orderBy('country')
            ->pluck('country')
            ->whenEmpty(fn () => collect(['Kenya']));
    }

    public function allConstituencies(?string $county = null): Collection
    {
        return Constituency::query()
            ->when($county, fn ($query) => $query->whereHas('county', fn ($countyQuery) => $countyQuery->where('name', $county)))
            ->orderBy('name')
            ->pluck('name');
    }

    public function allWards(?string $constituency = null): Collection
    {
        return Ward::query()
            ->when($constituency, fn ($query) => $query->whereHas('constituency', fn ($constituencyQuery) => $constituencyQuery->where('name', $constituency)))
            ->pluck('name')
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function filterPublic(array $filters, int $perPage = 16): LengthAwarePaginator
    {
        $query = $this->publicQuery($filters);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function publicCountyGroups(array $filters, int $limit = 5, bool $includeEmpty = false): Collection
    {
        $counties = $this->countiesForPublicFilters($filters);

        return $counties
            ->map(function (string $county) use ($filters, $limit) {
                $countyFilters = array_merge($filters, ['county' => $county]);
                unset($countyFilters['bloc']);

                $baseQuery = $this->publicQuery($countyFilters);
                $countyModel = County::where('name', $county)->first();

                return [
                    'label' => $county,
                    'county' => $county,
                    'filter_key' => 'county',
                    'filter_value' => $county,
                    'image' => $countyModel?->image,
                    'image_url' => $countyModel?->image ? Storage::url($countyModel->image) : null,
                    'total' => (clone $baseQuery)->count(),
                    'candidates' => $baseQuery->latest()->take($limit)->get(),
                ];
            })
            ->when(! $includeEmpty, fn ($groups) => $groups->filter(fn (array $group) => $group['total'] > 0))
            ->values();
    }

    public function publicConstituencyGroups(array $filters, int $limit = 5, bool $includeEmpty = false): Collection
    {
        $constituencies = $this->constituenciesForPublicFilters($filters);

        return $constituencies
            ->map(function (string $constituency) use ($filters, $limit) {
                $groupFilters = array_merge($filters, ['constituency' => $constituency]);

                $baseQuery = $this->publicQuery($groupFilters);
                $constituencyModel = Constituency::where('name', $constituency)->first();

                return [
                    'label' => $constituency,
                    'constituency' => $constituency,
                    'filter_key' => 'constituency',
                    'filter_value' => $constituency,
                    'image' => $constituencyModel?->image,
                    'image_url' => $constituencyModel?->image ? Storage::url($constituencyModel->image) : null,
                    'total' => (clone $baseQuery)->count(),
                    'candidates' => $baseQuery->latest()->take($limit)->get(),
                ];
            })
            ->when(! $includeEmpty, fn ($groups) => $groups->filter(fn (array $group) => $group['total'] > 0))
            ->values();
    }

    public function publicWardGroups(array $filters, int $limit = 5, bool $includeEmpty = false): Collection
    {
        $wards = $this->wardsForPublicFilters($filters);

        return $wards
            ->map(function (string $ward) use ($filters, $limit) {
                $groupFilters = array_merge($filters, ['ward' => $ward]);

                $baseQuery = $this->publicQuery($groupFilters);
                $wardModel = Ward::where('name', $ward)->first();

                return [
                    'label' => $ward,
                    'ward' => $ward,
                    'filter_key' => 'ward',
                    'filter_value' => $ward,
                    'image' => $wardModel?->image,
                    'image_url' => $wardModel?->image ? Storage::url($wardModel->image) : null,
                    'total' => (clone $baseQuery)->count(),
                    'candidates' => $baseQuery->latest()->take($limit)->get(),
                ];
            })
            ->when(! $includeEmpty, fn ($groups) => $groups->filter(fn (array $group) => $group['total'] > 0))
            ->values();
    }

    private function publicQuery(array $filters)
    {
        $query = Candidate::with('position', 'politicalParty');

        if (Schema::hasColumn('candidates', 'approval_status')) {
            $query->where('approval_status', 'approved');
        }

        $candidate = $filters['candidate'] ?? $filters['search'] ?? null;
        if (!empty($candidate)) {
            $query->where(function ($query) use ($candidate) {
                $query->where('name', 'like', "%{$candidate}%")
                    ->orWhere('nick_name', 'like', "%{$candidate}%");
            });
        }

        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (!empty($filters['bloc']) && empty($filters['county'])) {
            $counties = $this->countiesForPublicFilters($filters);
            $query->whereIn('county', $counties->all());
        }

        if (!empty($filters['county'])) {
            $query->where('county', $filters['county']);
        }

        if (!empty($filters['constituency'])) {
            $query->where('constituency', $filters['constituency']);
        }

        if (!empty($filters['ward'])) {
            $query->where('ward', $filters['ward']);
        }

        if (!empty($filters['position'])) {
            $position = $filters['position'];

            if (is_numeric($position)) {
                $query->where('position_id', $position);
            } else {
                $positionAliases = [
                    'presidential' => ['presidential', 'president'],
                    'governor' => ['governor'],
                    'senator' => ['senator'],
                    'women-rep' => ['women rep', 'woman rep', 'women representative', 'woman representative'],
                    'mp' => ['mp', 'member of parliament'],
                    'mca' => ['mca', 'member of county assembly'],
                ];
                $positionKey = strtolower(str_replace('_', '-', trim($position)));
                $names = $positionAliases[$positionKey] ?? [str_replace('-', ' ', $positionKey)];

                $query->whereHas('position', function ($positionQuery) use ($names) {
                    $positionQuery->whereIn($positionQuery->getModel()->getTable() . '.name', $names);
                });
            }
        }

        if (!empty($filters['political_party'])) {
            $party = $filters['political_party'];

            if (is_numeric($party)) {
                $query->where('political_party_id', $party);
            } else {
                $query->whereHas('politicalParty', function ($partyQuery) use ($party) {
                    $partyQuery->where('name', 'like', "%{$party}%")
                        ->orWhere('abbreviation', 'like', "%{$party}%");
                });
            }
        }

        return $query;
    }

    private function countiesForPublicFilters(array $filters): Collection
    {
        if (!empty($filters['county'])) {
            return collect([$filters['county']]);
        }

        if (!empty($filters['bloc'])) {
            return County::where('bloc_id', $filters['bloc'])
                ->orderBy('name')
                ->pluck('name');
        }

        $query = Candidate::whereNotNull('county')
            ->where('county', '!=', '');

        if (Schema::hasColumn('candidates', 'approval_status')) {
            $query->where('approval_status', 'approved');
        }

        return $query
            ->distinct()
            ->orderBy('county')
            ->pluck('county');
    }

    private function constituenciesForPublicFilters(array $filters): Collection
    {
        if (!empty($filters['constituency'])) {
            return collect([$filters['constituency']]);
        }

        return Constituency::query()
            ->when(!empty($filters['county']), fn ($query) => $query->whereHas('county', fn ($countyQuery) => $countyQuery->where('name', $filters['county'])))
            ->orderBy('name')
            ->pluck('name');
    }

    private function wardsForPublicFilters(array $filters): Collection
    {
        if (!empty($filters['ward'])) {
            return collect([$filters['ward']]);
        }

        return Ward::query()
            ->when(!empty($filters['constituency']), fn ($query) => $query->whereHas('constituency', fn ($constituencyQuery) => $constituencyQuery->where('name', $filters['constituency'])))
            ->pluck('name')
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }
    public function loadPublicShow(Candidate $candidate): Candidate
    {
        $candidate->load('position', 'politicalParty');

        $candidate->setRelation(
            'relatedArticles',
            NewsArticle::with('tags')
                ->whereHas('candidates', fn ($q) => $q->where('candidates.id', $candidate->id))
                ->where('status', 'published')
                ->latest()
                ->take(6)
                ->get()
        );

        return $candidate;
    }
}




