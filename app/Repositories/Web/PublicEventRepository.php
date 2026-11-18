<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\PublicEventRepositoryInterface;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicket;
use Illuminate\Support\Collection;

class PublicEventRepository implements PublicEventRepositoryInterface
{
    public function upcoming(): Collection
    {
        return Event::query()
            ->active()
            ->approved()
            ->where('date', '>=', now())
            ->orderBy('date')
            ->get();
    }

    public function past(int $limit): Collection
    {
        return Event::query()
            ->active()
            ->approved()
            ->where('date', '<', now())
            ->orderByDesc('date')
            ->take($limit)
            ->get();
    }

    public function publishedBySlug(string $slug): Event
    {
        return Event::query()
            ->active()
            ->approved()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function ticketByCode(string $code): EventTicket
    {
        return EventTicket::query()
            ->with('registration.event')
            ->where('code', $code)
            ->firstOrFail();
    }

    public function registrationByCheckoutReference(string $reference): ?EventRegistration
    {
        return EventRegistration::query()
            ->where('checkout_reference', $reference)
            ->first();
    }
}
