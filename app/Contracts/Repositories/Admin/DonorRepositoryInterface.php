<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Donor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DonorRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Donor;

    public function update(Donor $donor, array $data): bool;

    public function delete(Donor $donor): bool;

    public function count(): int;

    public function sumCompletedAmount(): float;

    public function latestDonations(int $limit = 8): Collection;
}
