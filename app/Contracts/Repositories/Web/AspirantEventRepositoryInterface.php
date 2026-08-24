<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AspirantEventRepositoryInterface
{
    public function paginateForCreator(int $userId, int $perPage = 12): LengthAwarePaginator;

    public function create(array $data): Event;

    public function slugExists(string $slug): bool;
}
