<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\County;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CountyRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): County;

    public function update(County $county, array $data): County;

    public function delete(County $county): bool;

    public function getOrderedBlocs(): Collection;

    public function import(array $counties): int;

    public function all(): Collection;
}
