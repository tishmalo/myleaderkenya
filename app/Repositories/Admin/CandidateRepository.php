<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\NewsArticle;
use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CandidateRepository implements CandidateRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Candidate::with('position')->latest()->paginate($perPage);
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
        return Position::orderBy('name')->get();
    }

    public function allBlocs(): Collection
    {
        return Bloc::orderBy('name')->get();
    }

    public function allCounties(): Collection
    {
        return Candidate::whereNotNull('county')->distinct()->pluck('county');
    }

    public function filterPublic(array $filters, int $perPage = 16): LengthAwarePaginator
    {
        $query = Candidate::with('position', 'bloc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('nick_name', 'like', "%{$search}%");
        }

        if (!empty($filters['county'])) {
            $query->where('county', $filters['county']);
        }

        if (!empty($filters['position'])) {
            $query->where('position_id', $filters['position']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function loadPublicShow(Candidate $candidate): Candidate
    {
        $candidate->load('position', 'bloc');

        $candidate->setRelation(
            'relatedArticles',
            NewsArticle::with('categories')
                ->whereHas('candidates', fn ($q) => $q->where('candidates.id', $candidate->id))
                ->where('status', 'published')
                ->latest()
                ->take(6)
                ->get()
        );

        return $candidate;
    }
}
