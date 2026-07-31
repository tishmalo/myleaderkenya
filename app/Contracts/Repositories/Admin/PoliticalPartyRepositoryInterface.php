<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\PoliticalParty;
use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PoliticalPartyRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function published(int $perPage = 12): LengthAwarePaginator;

    public function publishedForNav(): Collection;

    public function findPublishedBySlug(string $slug): PoliticalParty;

    public function positionsWithApprovedCandidates(
        PoliticalParty $politicalParty,
    ): Collection;

    public function paginateApprovedCandidatesForPosition(
        PoliticalParty $politicalParty,
        Position $position,
        int $perPage,
        string $pageName,
    ): LengthAwarePaginator;

    public function create(array $data): PoliticalParty;

    public function update(PoliticalParty $politicalParty, array $data): bool;

    public function delete(PoliticalParty $politicalParty): bool;
}
