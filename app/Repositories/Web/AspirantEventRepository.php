<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\AspirantEventRepositoryInterface;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AspirantEventRepository implements AspirantEventRepositoryInterface
{
    public function paginateForCreator(int $userId, int $perPage = 12): LengthAwarePaginator
    {
        return Event::query()
            ->withCount('registrations')
            ->where('created_by', $userId)
            ->orderByRaw("CASE approval_status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function slugExists(string $slug): bool
    {
        return Event::where('slug', $slug)->exists();
    }
}
