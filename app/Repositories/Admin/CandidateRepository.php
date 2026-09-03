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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CandidateRepository implements CandidateRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Candidate::with(['position', 'politicalParty', 'user', 'creatorAudit.user', 'claimRequests.user.relatedCandidates', 'claimRequests.reviewer', 'linkedCandidate:id,name'])
            ->withSum([
                'supportPayments as paid_support_gross_sum' => fn ($query) => $query->where('status', 'paid'),
            ], 'gross_amount')
            ->withCount([
                'supportPayments as paid_support_count' => fn ($query) => $query->where('status', 'paid'),
            ])
->withCount([
                'claimRequests as pending_claim_requests_count' => fn ($query) => $query->where('status', 'pending'),
                'claimRequests as approved_claim_requests_count' => fn ($query) => $query->where('status', 'approved'),
                'claimRequests as rejected_claim_requests_count' => fn ($query) => $query->where('status', 'rejected'),
            ]);

        $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function exportQuery(array $filters = []): Builder
    {
        return $this->applyFilters(Candidate::query(), $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['candidate'])) {
            $candidate = $filters['candidate'];
            $query->where(function ($query) use ($candidate) {
                $query->where('name', 'like', "%{$candidate}%")
                    ->orWhere('nick_name', 'like', "%{$candidate}%");
            });
        }

        if (! empty($filters['import_filter']) && Schema::hasColumn('candidates', 'is_imported')) {
            match ($filters['import_filter']) {
                'imported' => $query->where('is_imported', true)
                    ->where(function ($q) {
                        $q->whereNull('import_status')
                            ->orWhere('import_status', '!=', 'discarded');
                    }),
                'imported_pending' => $query->where('is_imported', true)->where('import_status', 'pending'),
                'imported_published' => $query->where('is_imported', true)->where('import_status', 'published'),
                'not_imported' => $query->where(function ($q) {
                    $q->where('is_imported', false)->orWhereNull('is_imported');
                }),
                default => null,
            };
        }

        // Hide discarded imports from the default list
        if (Schema::hasColumn('candidates', 'import_status')) {
            $query->where(function ($q) {
                $q->whereNull('import_status')
                    ->orWhere('import_status', '!=', 'discarded');
            });
        }

        if (!empty($filters['position'])) {
            $query->where('position_id', $filters['position']);
        }

        if (!empty($filters['political_party'])) {
            $query->where('political_party_id', $filters['political_party']);
        }

        if (!empty($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        if (!empty($filters['account_claim'])) {
            match ($filters['account_claim']) {
                'claimed_pending' => $query->whereHas('claimRequests', fn ($q) => $q->where('relationship', 'aspirant')->where('status', 'pending')),
                'claimed_approved' => $query->where(function ($q) {
                    $q->whereNotNull('claimed_at')
                        ->orWhereHas('claimRequests', fn ($cq) => $cq->where('relationship', 'aspirant')->where('status', 'approved'));
                }),
                'claim_sent' => $query->whereNull('claimed_at')
                    ->whereNotNull('claim_sent_at')
                    ->whereDoesntHave('claimRequests', fn ($q) => $q->where('relationship', 'aspirant')->whereIn('status', ['pending', 'approved'])),
                'unclaimed' => $query->whereNull('claimed_at')
                    ->whereNull('claim_sent_at')
                    ->whereDoesntHave('claimRequests', fn ($q) => $q->where('relationship', 'aspirant')),
                default => null,
            };
        }

        return $query;
    }

    public function find(int $id): ?Candidate
    {
        return Candidate::query()->find($id);
    }

    public function findPotentialDuplicate(array $data): ?Candidate
    {
        if (! empty($data['user_id'])) {
            $owned = Candidate::where('user_id', $data['user_id'])->first();
            if ($owned) {
                return $owned;
            }
        }

        $name = preg_replace('/\s+/', ' ', strtolower(trim((string) ($data['name'] ?? ''))));
        if ($name === '' || empty($data['position_id'])) {
            return null;
        }

        $query = Candidate::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$name])
            ->where('position_id', $data['position_id']);

        if (array_key_exists('political_party_id', $data)) {
            empty($data['political_party_id'])
                ? $query->whereNull('political_party_id')
                : $query->where('political_party_id', $data['political_party_id']);
        }

        $locationField = collect(['ward', 'constituency', 'county'])
            ->first(fn (string $field): bool => filled($data[$field] ?? null));

        if ($locationField) {
            $location = strtolower(trim((string) $data[$locationField]));
            $query->where(function ($locationQuery) use ($locationField, $location): void {
                $locationQuery->whereRaw('LOWER(TRIM('.$locationField.')) = ?', [$location])
                    ->orWhere(function ($incompleteQuery): void {
                        $incompleteQuery->whereNull('county')->whereNull('constituency')->whereNull('ward');
                    });
            });
        }

        return $query->oldest('id')->first();
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

    public function paginateApprovedForApi(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->publicQuery($filters);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function filterPublic(array $filters, int $perPage = 30): LengthAwarePaginator
    {
        $query = $this->publicQuery($filters);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function publicPositionGroups(array $filters, int $perPage = 20): Collection
    {
        $positionIds = $this->publicQuery($filters)
            ->whereNotNull('position_id')
            ->reorder()
            ->distinct()
            ->pluck('position_id');

        return Position::query()
            ->whereIn('id', $positionIds)
            ->ordered()
            ->get()
            ->map(function (Position $position) use ($filters, $perPage): array {
                $positionFilters = array_merge($filters, ['position' => $position->id]);
                $pageName = 'position_'.$position->id.'_page';
                $candidates = $this->publicQuery($positionFilters)
                    ->latest('created_at')
                    ->latest('id')
                    ->paginate($perPage, ['*'], $pageName)
                    ->appends($filters)
                    ->fragment('position-'.$position->id);

                return [
                    'position' => $position,
                    'label' => $position->name,
                    'total' => $candidates->total(),
                    'candidates' => $candidates,
                ];
            });
    }
    public function publicCountyGroups(array $filters, int $limit = 5, bool $includeEmpty = false, bool $withCandidates = true): Collection
    {
        $counties = $includeEmpty ? $this->allCountyNamesForPublicFilters($filters) : $this->countiesForPublicFilters($filters);

        return $counties
            ->map(function (string $county) use ($filters, $limit, $withCandidates) {
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
                    'candidates' => $withCandidates ? $baseQuery->latest()->take($limit)->get() : collect(),
                ];
            })
            ->when(! $includeEmpty, fn ($groups) => $groups->filter(fn (array $group) => $group['total'] > 0))
            ->values();
    }

    public function publicAlternativeCountyGroups(array $filters, string $currentCounty): Collection
    {
        unset($filters['county'], $filters['constituency'], $filters['ward'], $filters['bloc']);

        $counts = $this->publicQuery($filters)
            ->whereNotNull('county')
            ->where('county', '!=', '')
            ->where('county', '!=', $currentCounty)
            ->reorder()
            ->selectRaw('county, COUNT(*) as total')
            ->groupBy('county')
            ->orderBy('county')
            ->get();

        $countyModels = County::query()
            ->whereIn('name', $counts->pluck('county'))
            ->get(['name', 'image'])
            ->keyBy('name');

        return $counts->map(function ($count) use ($countyModels): array {
            $county = $countyModels->get($count->county);

            return [
                'label' => $count->county,
                'filter_value' => $count->county,
                'image_url' => $county?->image ? Storage::url($county->image) : null,
                'total' => (int) $count->total,
            ];
        })->values();
    }

    public function publicConstituencyGroups(array $filters, int $limit = 5, bool $includeEmpty = false, bool $withCandidates = true): Collection
    {
        $constituencies = $this->constituenciesForPublicFilters($filters);

        return $constituencies
            ->map(function (string $constituency) use ($filters, $limit, $withCandidates) {
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
                    'candidates' => $withCandidates ? $baseQuery->latest()->take($limit)->get() : collect(),
                ];
            })
            ->when(! $includeEmpty, fn ($groups) => $groups->filter(fn (array $group) => $group['total'] > 0))
            ->values();
    }

    public function publicWardGroups(array $filters, int $limit = 5, bool $includeEmpty = false, bool $withCandidates = true): Collection
    {
        $wards = $this->wardsForPublicFilters($filters);

        return $wards
            ->map(function (string $ward) use ($filters, $limit, $withCandidates) {
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
                    'candidates' => $withCandidates ? $baseQuery->latest()->take($limit)->get() : collect(),
                ];
            })
            ->when(! $includeEmpty, fn ($groups) => $groups->filter(fn (array $group) => $group['total'] > 0))
            ->values();
    }

    private function publicQuery(array $filters)
    {
        $query = Candidate::with('position', 'politicalParty')->where('approval_status', 'approved');

        if (array_key_exists('featured', $filters) && $filters['featured'] !== null) {
            $query->where('featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN));
        }

        $candidate = $filters['candidate'] ?? $filters['search'] ?? null;
        if (!empty($candidate)) {
            $query->where(function ($query) use ($candidate) {
                $query->where('name', 'like', "%{$candidate}%")
                    ->orWhere('nick_name', 'like', "%{$candidate}%")
                    ->orWhere('about', 'like', "%{$candidate}%");
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

        if (!empty($filters['bloc'])) {
            $blocCountyNames = $this->countyNamesForBloc($filters['bloc']);

            if ($blocCountyNames->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('county', $blocCountyNames->all());
            }
        }

        if (!empty($filters['constituency'])) {
            $query->where('constituency', $filters['constituency']);
        }

        if (!empty($filters['ward'])) {
            $query->where('ward', $filters['ward']);
        }

        if (!empty($filters['position'])) {
            $position = trim((string) $filters['position']);

            if (in_array(strtolower($position), ['all', 'any'], true)) {
                return $query;
            }

            if (is_numeric($position)) {
                $query->where('position_id', $position);
            } else {
                $names = $this->positionFilterNames($position);

                $query->whereHas('position', function ($positionQuery) use ($names) {
                    $positionQuery->whereIn(
                        DB::raw('LOWER(' . $positionQuery->getModel()->getTable() . '.name)'),
                        $names
                    );
                });
            }
        }

        if (!empty($filters['political_party'])) {
            $party = $filters['political_party'];

            if (is_numeric($party)) {
                $query->where('political_party_id', $party);
            } else {
                $query->whereHas('politicalParty', function ($partyQuery) use ($party) {
                    $partyQuery->where('slug', $party)
                        ->orWhere('name', 'like', "%{$party}%")
                        ->orWhere('abbreviation', 'like', "%{$party}%");
                });
            }
        }

        if (! $this->filtersTargetPresidential($filters)) {
            $query->orderByRaw("CASE WHEN county IS NULL OR county = '' THEN 1 ELSE 0 END")
                ->orderBy('county');
        }

        return $query;
    }

    private function positionFilterNames(string $position): array
    {
        $positionKey = strtolower(str_replace(['_', ' '], '-', trim($position)));

        $positionAliases = [
            'president' => ['presidential', 'president'],
            'presidential' => ['presidential', 'president'],
            'governor' => ['governor'],
            'senator' => ['senator'],
            'women-rep' => ['women rep', 'woman rep', 'women representative', 'woman representative'],
            'woman-rep' => ['women rep', 'woman rep', 'women representative', 'woman representative'],
            'women-representative' => ['women rep', 'woman rep', 'women representative', 'woman representative'],
            'woman-representative' => ['women rep', 'woman rep', 'women representative', 'woman representative'],
            'mp' => ['mp', 'member of parliament'],
            'member-of-parliament' => ['mp', 'member of parliament'],
            'mca' => ['mca', 'member of county assembly'],
            'member-of-county-assembly' => ['mca', 'member of county assembly'],
        ];

        return $positionAliases[$positionKey] ?? [str_replace('-', ' ', $positionKey)];
    }

    private function allCountyNamesForPublicFilters(array $filters): Collection
    {
        if (! empty($filters['county'])) {
            return collect([$filters['county']]);
        }

        if (! empty($filters['bloc'])) {
            return $this->countyNamesForBloc($filters['bloc']);
        }

        return County::query()
            ->orderBy('name')
            ->pluck('name');
    }

    private function countiesForPublicFilters(array $filters): Collection
    {
        if (!empty($filters['county'])) {
            return collect([$filters['county']]);
        }

        if (!empty($filters['bloc'])) {
            return $this->countyNamesForBloc($filters['bloc']);
        }

        return Candidate::whereNotNull('county')
            ->where('county', '!=', '')
            ->where('approval_status', 'approved')
            ->distinct()
            ->orderBy('county')
            ->pluck('county');
    }

    /**
     * Resolve counties assigned through either supported bloc relationship.
     */
    private function countyNamesForBloc(int|string $blocId): Collection
    {
        return County::query()
            ->where(function ($query) use ($blocId) {
                $query->where('bloc_id', $blocId);

                if (Schema::hasTable('bloc_county')) {
                    $query->orWhereHas('blocs', fn ($blocQuery) => $blocQuery->whereKey($blocId));
                }
            })
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values();
    }

    private function filtersTargetPresidential(array $filters): bool
    {
        if (empty($filters['position'])) {
            return false;
        }

        $position = $filters['position'];

        if (is_numeric($position)) {
            $position = Position::whereKey($position)->value('name');
        }

        if (! is_string($position)) {
            return false;
        }

        $position = strtolower(str_replace(['_', '-'], ' ', trim($position)));

        return str_contains($position, 'president');
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
        $candidate->load([
            'position',
            'politicalParty',
            'campaignPriorities' => fn ($query) => $query->where('status', 'approved')->with(['category' => fn ($categoryQuery) => $categoryQuery->where('is_active', true)])->orderBy('sort_order'),
            'parliamentMember' => fn ($query) => $query->where('is_published', true)->where('detail_status', 'complete')->with(['committees', 'activities']),
        ]);

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

