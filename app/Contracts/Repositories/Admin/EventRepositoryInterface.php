<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Event;

    public function update(Event $event, array $data): bool;

    public function delete(Event $event): bool;

    public function paginateRegistrations(Event $event, int $perPage = 20): LengthAwarePaginator;

    public function slugExists(string $slug, ?int $ignoringId = null): bool;
}
