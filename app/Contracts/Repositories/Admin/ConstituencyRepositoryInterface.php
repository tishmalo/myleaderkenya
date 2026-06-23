<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Constituency;
use App\Models\County;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ConstituencyRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Constituency;

    public function getOrderedCounties(): Collection;

    public function update(Constituency $constituency, array $data): bool;

    public function delete(Constituency $constituency): bool;

    public function import(array $constituencies): int;

    public function all(): Collection;
}
