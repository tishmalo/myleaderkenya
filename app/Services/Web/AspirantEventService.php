<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\AspirantEventRepositoryInterface;
use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AspirantEventService
{
    public function __construct(private AspirantEventRepositoryInterface $events) {}

    public function listFor(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return $this->events->paginateForCreator($user->getKey(), $perPage);
    }

    public function submit(User $user, array $data, ?UploadedFile $poster = null): Event
    {
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['is_active'] = true;
        $data['approval_status'] = Event::STATUS_PENDING;
        $data['created_by'] = $user->getKey();

        if ($poster) {
            $data['poster'] = $poster->store('events/posters', 'public');
        }

        return $this->events->create($data);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'event';
        $slug = $base;
        $suffix = 2;

        while ($this->events->slugExists($slug)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
