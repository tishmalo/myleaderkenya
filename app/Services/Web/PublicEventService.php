<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\PublicEventRepositoryInterface;
use App\Models\Event;
use App\Models\EventTicket;
use App\Services\SeoService;
use Illuminate\Support\Str;

/**
 * Everything the public event pages need: the events to list, the event being
 * viewed and the SEO metadata that goes with it.
 */
class PublicEventService
{
    private const INDEX_ROUTE = 'events.public';

    private const DEFAULT_IMAGE = 'images/myleader.png';

    public function __construct(
        private PublicEventRepositoryInterface $events,
        private SeoService $seo,
    ) {}

    /**
     * @return array{upcomingEvents: \Illuminate\Support\Collection, pastEvents: \Illuminate\Support\Collection}
     */
    public function indexData(): array
    {
        $this->seo
            ->setTitle('Kenya 2027 Election Events & Campaign Rallies | My Leader Kenya')
            ->setDescription('Upcoming and past campaign events, rallies and meetings for Kenya 2027 aspirants. Register and follow events on My Leader Kenya.')
            ->setCanonical(route(self::INDEX_ROUTE));

        return [
            'upcomingEvents' => $this->events->upcoming(),
            'pastEvents' => $this->events->past(6),
        ];
    }

    /**
     * @return array{event: Event}
     */
    public function showData(string $slug): array
    {
        $event = $this->publishedEvent($slug);

        $this->applySeo($event);

        return ['event' => $event];
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function publishedEvent(string $slug): Event
    {
        return $this->events->publishedBySlug($slug);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function ticket(string $code): EventTicket
    {
        return $this->events->ticketByCode($code);
    }

    private function applySeo(Event $event): void
    {
        $title = "{$event->title} | Kenya 2027 Event";

        if (mb_strlen($title) > 60) {
            $title = Str::limit($event->title, 50, '').' | 2027';
        }

        $location = $event->location ?: 'Kenya';
        $image = $this->imageUrl($event);

        $this->seo
            ->setTitle($title)
            ->setDescription("Join {$event->title} on ".optional($event->date)->format('d M Y')." in {$location}. Register for this Kenya 2027 campaign event on My Leader Kenya.")
            ->setCanonical(route('events.show', $event->slug))
            ->setOg([
                'title' => $title,
                'type' => 'event',
                'image' => $image,
            ]);

        $this->seo->addSchema([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->title,
            'description' => strip_tags($event->description ?? "Kenya 2027 campaign event: {$event->title}"),
            'url' => route('events.show', $event->slug),
            'startDate' => $event->date?->toIso8601String(),
            'endDate' => $event->end_date?->toIso8601String() ?? $event->date?->toIso8601String(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => [
                '@type' => 'Place',
                'name' => $location,
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $location,
                    'addressCountry' => 'KE',
                ],
            ],
            'image' => $image,
            'organizer' => [
                '@type' => 'Organization',
                'name' => 'My Leader Kenya',
                'url' => url('/'),
            ],
        ]);
    }

    private function imageUrl(Event $event): string
    {
        return $event->poster
            ? asset('storage/'.$event->poster)
            : asset(self::DEFAULT_IMAGE);
    }
}
