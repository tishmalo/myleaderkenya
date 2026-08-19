@extends('layouts.app')

@section('page_title', 'Edit Event')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('events.index') }}" class="text-zinc-400 hover:text-white transition">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <h1 class="text-3xl font-semibold text-white">Edit Event</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-2xl p-4 mb-6">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form method="POST" action="{{ route('events.update', $event) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-2">
                <label for="title" class="text-sm font-medium text-zinc-300">Event Title</label>
                <input type="text" id="title" name="title" value="{{ old('title', $event->title) }}" required placeholder="e.g. Town Hall Forum with Aspirants"
                       class="bg-zinc-855 border border-zinc-700 rounded-2xl px-5 py-3 text-white focus:outline-none focus:border-emerald-500 bg-zinc-800">
            </div>

            <div class="flex flex-col gap-2">
                <label for="description" class="text-sm font-medium text-zinc-300">Description</label>
                <textarea id="description" name="description" rows="5" required placeholder="Describe the event, agenda, and speakers..."
                          class="bg-zinc-855 border border-zinc-700 rounded-2xl px-5 py-3 text-white focus:outline-none focus:border-emerald-500 bg-zinc-800">{{ old('description', $event->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2">
                    <label for="poster" class="text-sm font-medium text-zinc-300">Event Poster / Image</label>
                    @if($event->poster)
                        <img src="{{ asset('storage/' . $event->poster) }}" alt="Current event poster"
                             class="w-full max-w-xs aspect-video object-cover rounded-2xl border border-zinc-700">
                    @endif
                    <input type="file" id="poster" name="poster" accept="image/*"
                           class="block w-full text-sm text-zinc-400 file:mr-4 file:rounded-2xl file:border-0 file:bg-emerald-600 file:px-5 file:py-3 file:text-sm file:font-medium file:text-white hover:file:bg-emerald-700 bg-zinc-800 border border-zinc-700 rounded-2xl">
                    <p class="text-xs text-zinc-500">Optional. JPG, PNG, WEBP or GIF (max 5MB).</p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="promo_video" class="text-sm font-medium text-zinc-300">Event Promo Video (YouTube)</label>
                    <input type="url" id="promo_video" name="promo_video" value="{{ old('promo_video', $event->promo_video) }}" placeholder="https://www.youtube.com/watch?v=..."
                           class="bg-zinc-855 border border-zinc-700 rounded-2xl px-5 py-3 text-white focus:outline-none focus:border-emerald-500 bg-zinc-800">
                    <p class="text-xs text-zinc-500">Optional. Paste a YouTube video link.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2">
                    <label for="date" class="text-sm font-medium text-zinc-300">Date & Time</label>
                    <input type="datetime-local" id="date" name="date" value="{{ old('date', $event->date ? $event->date->format('Y-m-d\TH:i') : '') }}" required
                           class="bg-zinc-855 border border-zinc-700 rounded-2xl px-5 py-3 text-white focus:outline-none focus:border-emerald-500 bg-zinc-800">
                </div>

                <div class="flex flex-col gap-2">
                    <label for="location" class="text-sm font-medium text-zinc-300">Location</label>
                    <input type="text" id="location" name="location" value="{{ old('location', $event->location) }}" required placeholder="e.g. KICC, Nairobi or Online"
                           class="bg-zinc-855 border border-zinc-700 rounded-2xl px-5 py-3 text-white focus:outline-none focus:border-emerald-500 bg-zinc-800">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2">
                    <label for="price" class="text-sm font-medium text-zinc-300">Price (KES)</label>
                    <input type="number" id="price" name="price" value="{{ old('price', $event->price) }}" min="0" step="0.01" required
                           class="bg-zinc-855 border border-zinc-700 rounded-2xl px-5 py-3 text-white focus:outline-none focus:border-emerald-500 bg-zinc-800">
                </div>

                <div class="flex items-center gap-3 mt-8">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $event->is_active) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-zinc-700 text-emerald-600 bg-zinc-850 accent-emerald-500">
                    <label for="is_active" class="text-sm font-medium text-zinc-300 select-none">Active / Visible on site</label>
                </div>
            </div>

            <div class="pt-6 border-t border-zinc-800 flex justify-end gap-3">
                <a href="{{ route('events.index') }}"
                   class="bg-zinc-800 hover:bg-zinc-750 hover:bg-zinc-700 px-6 py-3 rounded-2xl text-sm font-medium text-zinc-300 transition">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium text-white transition">
                    Update Event
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
