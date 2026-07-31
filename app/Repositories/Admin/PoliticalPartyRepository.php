<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\PoliticalPartyRepositoryInterface;
use App\Models\Candidate;
use App\Models\PoliticalParty;
use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PoliticalPartyRepository implements PoliticalPartyRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PoliticalParty::query();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
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

    public function positionsWithApprovedCandidates(
        PoliticalParty $politicalParty,
    ): Collection {
        $positionIds = Candidate::query()
            ->select('position_id')
            ->where('political_party_id', $politicalParty->id)
            ->where('approval_status', 'approved')
            ->whereNotNull('position_id');

        return Position::query()
            ->whereIn('id', $positionIds)
            ->ordered()
            ->get();
    }

    public function paginateApprovedCandidatesForPosition(
        PoliticalParty $politicalParty,
        Position $position,
        int $perPage,
        string $pageName,
    ): LengthAwarePaginator {
        return $politicalParty->candidates()
            ->with(['position', 'politicalParty'])
            ->where('approval_status', 'approved')
            ->where('position_id', $position->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
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
