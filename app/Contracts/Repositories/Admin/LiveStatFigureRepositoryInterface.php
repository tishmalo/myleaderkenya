<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\LiveStatFigure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface LiveStatFigureRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function batches(): Collection;

    public function activeTotals(): array;

    public function create(array $data): LiveStatFigure;

    public function delete(LiveStatFigure $figure): bool;

    public function deleteBatch(string $batchId): int;
}

