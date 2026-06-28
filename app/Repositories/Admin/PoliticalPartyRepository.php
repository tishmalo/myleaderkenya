<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\PoliticalPartyRepositoryInterface;
use App\Models\PoliticalParty;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PoliticalPartyRepository implements PoliticalPartyRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PoliticalParty::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('abbreviation', 'like', "%{$filters['search']}%")
                  ->orWhere('content', 'like', "%{$filters['search']}%");
            });
        }

        return $query->ordered()->paginate($perPage);
    }

    public function published(int $perPage = 12): LengthAwarePaginator
    {
        return PoliticalParty::published()->ordered()->paginate($perPage);
    }

    public function publishedForNav(): Collection
    {
        return PoliticalParty::published()->ordered()->get();
    }

    public function findPublishedBySlug(string $slug): PoliticalParty
    {
        return PoliticalParty::with(['coalitions' => fn ($q) => $q->published()->ordered()])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function create(array $data): PoliticalParty
    {
        return PoliticalParty::create($data);
    }

    public function update(PoliticalParty $politicalParty, array $data): bool
    {
        return $politicalParty->update($data);
    }

    public function delete(PoliticalParty $politicalParty): bool
    {
        return $politicalParty->delete();
    }
}
