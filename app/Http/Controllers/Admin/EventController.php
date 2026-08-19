<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount('registrations')->orderBy('date', 'desc')->paginate(15);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'promo_video' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:204800',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('poster')) {
            $validated['poster'] = $request->file('poster')->store('events/posters', 'public');
        }

        if ($request->hasFile('promo_video')) {
            $validated['promo_video'] = $request->file('promo_video')->store('events/videos', 'public');
        }

        // Generate unique slug
        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;
        while (Event::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        $validated['slug'] = $slug;

        Event::create($validated);

        return redirect()->route('events.index')
                         ->with('success', 'Event created successfully!');
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'promo_video' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:204800',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('poster')) {
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }
            $validated['poster'] = $request->file('poster')->store('events/posters', 'public');
        }

        if ($request->hasFile('promo_video')) {
            if ($event->promo_video) {
                Storage::disk('public')->delete($event->promo_video);
            }
            $validated['promo_video'] = $request->file('promo_video')->store('events/videos', 'public');
        }

        if ($validated['title'] !== $event->title) {
            $slug = Str::slug($validated['title']);
            $originalSlug = $slug;
            $counter = 1;
            while (Event::where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $event->update($validated);

        return redirect()->route('events.index')
                         ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully.'
        ]);
    }

    public function registrations(Event $event)
    {
        $registrations = $event->registrations()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.events.registrations', compact('event', 'registrations'));
    }
}
