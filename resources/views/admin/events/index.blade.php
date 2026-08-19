@extends('layouts.app')

@section('page_title', 'Events Portal')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-calendar-alt text-emerald-500"></i>
            Events Management
        </h1>
        <a href="{{ route('events.create') }}"
           class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i> New Event
        </a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr>
                    <th class="px-6 py-4 text-left">Event Details</th>
                    <th class="px-6 py-4 text-left">Location</th>
                    <th class="px-6 py-4 text-center">Date & Time</th>
                    <th class="px-6 py-4 text-center">Price</th>
                    <th class="px-6 py-4 text-center">Registrants</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 text-zinc-300">
                @forelse($events as $event)
                <tr class="hover:bg-zinc-800/70">
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-white">{{ $event->title }}</p>
                            <p class="text-xs text-zinc-500 mt-1">{{ Str::limit(strip_tags($event->description), 50) }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-zinc-400">{{ $event->location }}</td>
                    <td class="px-6 py-4 text-center text-zinc-400">
                        {{ $event->date->format('M d, Y h:i A') }}
                    </td>
                    <td class="px-6 py-4 text-center font-semibold text-white">
                        KES {{ number_format($event->price, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('events.registrations', $event) }}" class="text-emerald-400 hover:underline">
                            {{ $event->registrations_count }} Registrants
                        </a>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($event->is_active)
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-emerald-500/20 text-emerald-400">Active</span>
                        @else
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-zinc-500/20 text-zinc-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('events.edit', $event) }}" class="text-blue-400 hover:text-blue-500 mx-2" aria-label="Edit {{ $event->title }}">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteEvent('{{ route('events.destroy', $event) }}', '{{ addslashes($event->title) }}')"
                                class="text-red-400 hover:text-red-500 mx-2" aria-label="Delete {{ $event->title }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-16 text-zinc-500">No events found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-center">
        {{ $events->appends(request()->query())->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteEvent(url, title) {
    showDeleteModal(url, `Delete event <strong>${title}</strong>? All registrations will be removed.`);
}
</script>
@endpush
