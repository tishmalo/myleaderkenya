<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\PositionRepositoryInterface;
use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PositionRepository implements PositionRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Position::ordered()->paginate($perPage);
    }

    public function find(int $id): Position
    {
        return Position::findOrFail($id);
    }

    public function create(array $data): Position
    {
        return Position::create($data);
    }

    public function update(Position $position, array $data): bool
    {
        return $position->update($data);
    }

    public function delete(Position $position): bool
    {
        return $position->delete();
    }
}
