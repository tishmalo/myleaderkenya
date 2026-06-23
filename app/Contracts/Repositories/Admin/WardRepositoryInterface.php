<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Ward;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface WardRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Ward;

    public function update(Ward $ward, array $data): bool;

    public function delete(Ward $ward): bool;

    public function getOrderedConstituency():collection;

    public function import(array $wards): int;

    public function all(): Collection;
}
