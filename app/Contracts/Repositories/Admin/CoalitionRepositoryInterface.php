<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Coalition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CoalitionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function published(int $perPage = 12): LengthAwarePaginator;
    public function publishedForNav(): Collection;
    public function findPublishedBySlug(string $slug): Coalition;
    public function create(array $data): Coalition;
    public function update(Coalition $coalition, array $data): bool;
    public function delete(Coalition $coalition): bool;
    public function syncPoliticalParties(Coalition $coalition, array $partyIds): void;
    public function allPoliticalParties(): Collection;
}
