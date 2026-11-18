<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicket;
use Illuminate\Support\Collection;

interface PublicEventRepositoryInterface
{
    /**
     * Active, approved events that have not started yet, soonest first.
     *
     * @return Collection<int, Event>
     */
    public function upcoming(): Collection;

    /**
     * Active, approved events that have already taken place, most recent first.
     *
     * @return Collection<int, Event>
     */
    public function past(int $limit): Collection;

    /**
     * The public event behind a slug. Only active, approved events are public,
     * so anything else is treated as missing.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function publishedBySlug(string $slug): Event;

    /**
     * A ticket with the registration and event it belongs to eager loaded.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function ticketByCode(string $code): EventTicket;

    /**
     * The registration the payment gateway is reporting on, if we created it.
     */
    public function registrationByCheckoutReference(string $reference): ?EventRegistration;
}
