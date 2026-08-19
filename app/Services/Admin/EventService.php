<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\EventRepositoryInterface;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventService
{
    public function __construct(private EventRepositoryInterface $events) {}

    public function paginateEvents(int $perPage = 15): LengthAwarePaginator
    {
        return $this->events->paginate($perPage);
    }

    public function createEvent(array $data, ?UploadedFile $poster = null): Event
    {
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['is_active'] = ! empty($data['is_active']);

        if ($poster) {
            $data['poster'] = $poster->store('events/posters', 'public');
        }

        return $this->events->create($data);
    }

    public function updateEvent(Event $event, array $data, ?UploadedFile $poster = null): bool
    {
        if (($data['title'] ?? $event->title) !== $event->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $event->id);
        }

        $data['is_active'] = ! empty($data['is_active']);

        if ($poster) {
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }

            $data['poster'] = $poster->store('events/posters', 'public');
        }

        return $this->events->update($event, $data);
    }

    public function deleteEvent(Event $event): bool
    {
        if ($event->poster) {
            Storage::disk('public')->delete($event->poster);
        }

        return $this->events->delete($event);
    }

    public function paginateRegistrations(Event $event, int $perPage = 20): LengthAwarePaginator
    {
        return $this->events->paginateRegistrations($event, $perPage);
    }

    private function uniqueSlug(string $title, ?int $ignoringId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->events->slugExists($slug, $ignoringId)) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
