<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\CandidateTokenPackage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CandidateTokenPackageRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function active(): Collection;

    public function findActive(int $id): CandidateTokenPackage;

    public function create(array $data): CandidateTokenPackage;

    public function update(CandidateTokenPackage $package, array $data): bool;

    public function delete(CandidateTokenPackage $package): bool;
}
