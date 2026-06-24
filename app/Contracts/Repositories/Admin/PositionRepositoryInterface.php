<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PositionRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): Position;

    public function create(array $data): Position;

    public function update(Position $position, array $data): bool;

    public function delete(Position $position): bool;
}
