<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicket;
use App\Services\Admin\EventService;
use App\Services\Web\EventRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private EventService $events,
        private EventRegistrationService $registrations
    ) {}

    public function index()
    {
        return view('admin.events.index', [
            'events' => $this->events->paginateEvents(),
        ]);
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(StoreEventRequest $request)
    {
        $this->events->createEvent(
            $request->validated(),
            $request->file('poster'),
            $request->user()->getKey()
        );

        return redirect()->route('events.index')
            ->with('success', 'Event created successfully!');
    }

    public function updateApproval(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $this->events->reviewEvent($event, $validated['status'], $request->user()->getKey());

        return redirect()->back()
            ->with('success', $validated['status'] === 'approved'
                ? "Event \"{$event->title}\" approved and published."
                : "Event \"{$event->title}\" rejected.");
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $this->events->updateEvent($event, $request->validated(), $request->file('poster'));

        return redirect()->route('events.index')
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        $this->events->deleteEvent($event);

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully.'
        ]);
    }

    public function registrations(Event $event)
    {
        return view('admin.events.registrations', [
            'event' => $event,
            'registrations' => $this->events->paginateRegistrations($event),
        ]);
    }

    public function resendTicketEmail(Event $event, EventRegistration $registration): RedirectResponse
    {
        try {
            $this->registrations->sendTicketEmail($registration);

            return redirect()->back()
                ->with('success', 'Ticket email re-sent to ' . $registration->email . '.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function generateTickets(Event $event, EventRegistration $registration): RedirectResponse
    {
        $this->registrations->ensureTickets($registration);

        return redirect()->back()
            ->with('success', 'Tickets generated for ' . $registration->name . '.');
    }

    public function checkInTicket(Event $event, EventRegistration $registration, EventTicket $ticket): RedirectResponse
    {
        $checkedIn = $this->registrations->toggleCheckIn($ticket);

        return redirect()->back()
            ->with('success', $checkedIn ? 'Ticket marked as attended.' : 'Check-in reverted.');
    }
}
