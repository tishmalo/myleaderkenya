<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Models\PoliticalParty;
use App\Models\Candidate;
use App\Models\NewsArticle;
use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CandidateRepository implements CandidateRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Candidate::with(['position', 'politicalParty'])->latest()->paginate($perPage);
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
        return Candidate::whereNotNull('county')->distinct()->pluck('county');
    }

    public function filterPublic(array $filters, int $perPage = 16): LengthAwarePaginator
    {
        $query = Candidate::with('position', 'politicalParty');

        $candidate = $filters['candidate'] ?? $filters['search'] ?? null;
        if (!empty($candidate)) {
            $query->where(function ($query) use ($candidate) {
                $query->where('name', 'like', "%{$candidate}%")
                    ->orWhere('nick_name', 'like', "%{$candidate}%");
            });
        }

        if (!empty($filters['county'])) {
            $query->where('county', $filters['county']);
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

        return $query->latest()->paginate($perPage)->withQueryString();
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


