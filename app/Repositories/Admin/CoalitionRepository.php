<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CoalitionRepositoryInterface;
use App\Models\Coalition;
use App\Models\PoliticalParty;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CoalitionRepository implements CoalitionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Coalition::with('politicalParties');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('content', 'like', "%{$filters['search']}%");
            });
        }

        return $query->ordered()->paginate($perPage);
    }

    public function published(int $perPage = 12): LengthAwarePaginator
    {
        return Coalition::with(['politicalParties' => fn ($q) => $q->published()->ordered()])
            ->published()
            ->ordered()
            ->paginate($perPage);
    }

    public function publishedForNav(): Collection
    {
        return Coalition::published()->ordered()->get();
    }

    public function findPublishedBySlug(string $slug): Coalition
    {
        return Coalition::with(['politicalParties' => fn ($q) => $q->published()->ordered()])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function create(array $data): Coalition
    {
        return Coalition::create($data);
    }

    public function update(Coalition $coalition, array $data): bool
    {
        return $coalition->update($data);
    }

    public function delete(Coalition $coalition): bool
    {
        return $coalition->delete();
    }

    public function syncPoliticalParties(Coalition $coalition, array $partyIds): void
    {
        $coalition->politicalParties()->sync($partyIds);
    }

    public function allPoliticalParties(): Collection
    {
        return PoliticalParty::ordered()->get();
    }
}
