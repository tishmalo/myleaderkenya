@extends('layouts.app')

@section('page_title', 'Event Ticket Email Template')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto">
        <div class="bg-zinc-900 border border-zinc-800 overflow-hidden shadow-sm rounded-3xl">
            <div class="p-8 text-zinc-100">
                @if(session('success'))
                    <div class="mb-6 p-4 text-sm font-medium text-emerald-400 bg-emerald-500/10 rounded-2xl border border-emerald-500/20">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-white">Event Ticket Email Template</h1>
                    <p class="text-sm text-zinc-500 mt-1">Sent to attendees after a successful payment. Available placeholders:</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach(['{attendee_name}', '{event_title}', '{event_date}', '{event_location}', '{amount}'] as $placeholder)
                            <span class="px-2 py-1 text-xs font-mono bg-zinc-800 text-emerald-400 rounded-lg border border-zinc-700">{{ $placeholder }}</span>
                        @endforeach
                    </div>
                </div>

                <form action="{{ route('events.email-template.update') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="subject" class="block text-sm font-medium text-zinc-300">Subject</label>
                        <input type="text" id="subject" name="subject" value="{{ old('subject', $template['subject']) }}" required class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                        @error('subject')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="body" class="block text-sm font-medium text-zinc-300">Body (HTML)</label>
                        <textarea id="body" name="body" rows="12" required class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">{{ old('body', $template['body']) }}</textarea>
                        @error('body')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-medium text-white hover:bg-emerald-700">Save Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
