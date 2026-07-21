<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\CandidateTokenRate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CandidateTokenRateRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function active(): Collection;

    public function findActiveByActionKey(string $actionKey): ?CandidateTokenRate;

    public function create(array $data): CandidateTokenRate;

    public function update(CandidateTokenRate $rate, array $data): bool;

    public function delete(CandidateTokenRate $rate): bool;
}
