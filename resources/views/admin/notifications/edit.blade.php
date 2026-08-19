@extends('layouts.app')

@section('page_title', 'Edit Email Notification')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('notification-emails.index') }}" class="text-zinc-400 hover:text-white transition">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-white">{{ $notification['label'] }}</h1>
                <p class="text-sm text-zinc-500 mt-1">{{ $notification['description'] }}</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 text-sm font-medium text-emerald-400 bg-emerald-500/10 rounded-2xl border border-emerald-500/20">{{ session('success') }}</div>
        @endif

        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
            <div class="mb-6">
                <p class="text-sm text-zinc-400">Available placeholders:</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($notification['placeholders'] as $placeholder)
                        <span class="px-2 py-1 text-xs font-mono bg-zinc-800 text-emerald-400 rounded-lg border border-zinc-700">{{ $placeholder }}</span>
                    @endforeach
                </div>
            </div>

            <form action="{{ route('notification-emails.update', $notification['key']) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3">
                    <input type="hidden" name="enabled" value="0">
                    <input type="checkbox" id="enabled" name="enabled" value="1" {{ old('enabled', $notification['enabled']) ? 'checked' : '' }} class="h-4 w-4 rounded border-zinc-700 bg-zinc-950 text-emerald-600 focus:ring-emerald-500">
                    <label for="enabled" class="text-sm font-medium text-zinc-300">Enabled</label>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-zinc-300">Subject</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject', $notification['subject']) }}" required class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                    @error('subject')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="body" class="block text-sm font-medium text-zinc-300">Body (HTML)</label>
                    <textarea id="body" name="body" rows="12" required class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">{{ old('body', $notification['body']) }}</textarea>
                    @error('body')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('notification-emails.index') }}" class="px-6 py-3 rounded-2xl border border-zinc-700 text-sm font-medium text-zinc-300 hover:bg-zinc-800">Cancel</a>
                    <button type="submit" class="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-medium text-white hover:bg-emerald-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
