<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\EventRepositoryInterface;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventRepository implements EventRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Event::withCount('registrations')
            ->orderBy('date', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): bool
    {
        return $event->update($data);
    }

    public function delete(Event $event): bool
    {
        return $event->delete();
    }

    public function paginateRegistrations(Event $event, int $perPage = 20): LengthAwarePaginator
    {
        return $event->registrations()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function slugExists(string $slug, ?int $ignoringId = null): bool
    {
        return Event::where('slug', $slug)
            ->when($ignoringId, fn ($query) => $query->where('id', '!=', $ignoringId))
            ->exists();
    }
}
