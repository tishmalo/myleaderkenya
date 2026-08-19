<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Services\Admin\EventService;

class EventController extends Controller
{
    public function __construct(private EventService $events) {}

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
        $this->events->createEvent($request->validated(), $request->file('poster'));

        return redirect()->route('events.index')
            ->with('success', 'Event created successfully!');
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
}
