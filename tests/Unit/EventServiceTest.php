<?php

namespace Tests\Unit;

use App\Contracts\Repositories\Admin\EventRepositoryInterface;
use App\Models\Event;
use App\Services\Admin\EventService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    public function test_create_event_generates_slug_and_stores_poster(): void
    {
        Storage::fake('public');

        $repository = $this->createMock(EventRepositoryInterface::class);
        $repository->method('slugExists')->willReturn(false);
        $repository->method('create')->willReturnCallback(fn (array $data) => new Event($data));

        $service = new EventService($repository);

        $event = $service->createEvent([
            'title' => 'Town Hall Forum',
            'description' => 'A public town hall.',
            'date' => now()->addDay(),
            'location' => 'Nairobi',
            'price' => 3000,
            'promo_video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ], UploadedFile::fake()->image('poster.jpg'));

        $this->assertSame('town-hall-forum', $event->slug);
        $this->assertNotNull($event->poster);
        Storage::disk('public')->assertExists($event->poster);
    }

    public function test_create_event_appends_suffix_when_slug_is_taken(): void
    {
        $repository = $this->createMock(EventRepositoryInterface::class);
        $repository->method('slugExists')->willReturnOnConsecutiveCalls(true, false);
        $repository->method('create')->willReturnCallback(fn (array $data) => new Event($data));

        $service = new EventService($repository);

        $event = $service->createEvent(['title' => 'Town Hall'], null);

        $this->assertSame('town-hall-1', $event->slug);
    }

    public function test_create_event_defaults_is_active_to_false_when_absent(): void
    {
        $repository = $this->createMock(EventRepositoryInterface::class);
        $repository->method('slugExists')->willReturn(false);
        $repository->method('create')->willReturnCallback(fn (array $data) => new Event($data));

        $service = new EventService($repository);

        $event = $service->createEvent(['title' => 'Quiet Event'], null);

        $this->assertFalse($event->is_active);
    }

    public function test_update_event_regenerates_slug_only_when_title_changes(): void
    {
        $event = new Event(['title' => 'Old Title', 'slug' => 'old-title', 'is_active' => true]);

        $captured = null;
        $repository = $this->createMock(EventRepositoryInterface::class);
        $repository->method('slugExists')->willReturn(false);
        $repository->method('update')->willReturnCallback(function (Event $updated, array $data) use (&$captured) {
            $captured = $data;

            return true;
        });

        $service = new EventService($repository);

        $service->updateEvent($event, ['title' => 'New Title'], null);
        $this->assertSame('new-title', $captured['slug']);

        $service->updateEvent($event, ['title' => 'Old Title'], null);
        $this->assertArrayNotHasKey('slug', $captured);
    }

    public function test_update_event_replaces_poster_and_removes_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('events/posters/old.jpg', 'old');

        $event = new Event(['title' => 'Forum', 'slug' => 'forum', 'poster' => 'events/posters/old.jpg']);

        $captured = null;
        $repository = $this->createMock(EventRepositoryInterface::class);
        $repository->method('slugExists')->willReturn(false);
        $repository->method('update')->willReturnCallback(function (Event $updated, array $data) use (&$captured) {
            $captured = $data;

            return true;
        });

        $service = new EventService($repository);

        $service->updateEvent($event, ['title' => 'Forum'], UploadedFile::fake()->image('new.jpg'));

        Storage::disk('public')->assertMissing('events/posters/old.jpg');
        $this->assertNotNull($captured['poster']);
        Storage::disk('public')->assertExists($captured['poster']);
    }

    public function test_delete_event_removes_poster_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('events/posters/old.jpg', 'old');

        $event = new Event(['poster' => 'events/posters/old.jpg']);

        $repository = $this->createMock(EventRepositoryInterface::class);
        $repository->method('delete')->willReturn(true);

        $service = new EventService($repository);

        $service->deleteEvent($event);

        Storage::disk('public')->assertMissing('events/posters/old.jpg');
    }
}
