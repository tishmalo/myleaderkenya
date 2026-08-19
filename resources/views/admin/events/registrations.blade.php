@extends('layouts.app')

@section('page_title', 'Event Registrant Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('events.index') }}" class="text-zinc-400 hover:text-white transition">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <span class="text-xs text-emerald-400 font-semibold tracking-wider uppercase">Event Attendees</span>
            <h1 class="text-3xl font-semibold text-white">{{ $event->title }}</h1>
            <p class="text-sm text-zinc-500 mt-1">
                <i class="fas fa-calendar-alt mr-1"></i> {{ $event->date->format('M d, Y h:i A') }}
                <span class="mx-2">|</span>
                <i class="fas fa-map-marker-alt mr-1"></i> {{ $event->location }}
            </p>
        </div>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr>
                    <th class="px-6 py-4 text-left">Registrant</th>
                    <th class="px-6 py-4 text-left">Contact Info</th>
                    <th class="px-6 py-4 text-center">Audience Type</th>
                    <th class="px-6 py-4 text-center">Aspirancy Position</th>
                    <th class="px-6 py-4 text-center">Reference Codes</th>
                    <th class="px-6 py-4 text-center">Amount</th>
                    <th class="px-6 py-4 text-center">Payment Status</th>
                    <th class="px-6 py-4 text-center">Tickets / Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 text-zinc-300">
                @forelse($registrations as $reg)
                <tr class="align-top hover:bg-zinc-800/70 {{ $reg->payment_status === 'success' ? 'bg-emerald-500/10' : '' }}">
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-white">{{ $reg->name }}</p>
                            <p class="text-xs text-zinc-500 mt-1">Registered: {{ $reg->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-400">
                        <div>{{ $reg->email }}</div>
                        <div class="text-xs text-zinc-500 mt-0.5">{{ $reg->phone }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-zinc-800 text-zinc-300 border border-zinc-700">
                            {{ $reg->user_type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-zinc-800 text-zinc-300 border border-zinc-700">
                            {{ $reg->position }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center text-xs text-zinc-400">
                        <div>Ref: <span class="font-mono text-zinc-300">{{ $reg->checkout_reference }}</span></div>
                        @if($reg->payment_reference)
                            <div class="text-zinc-500 mt-0.5">Gateway: <span class="font-mono text-zinc-400">{{ $reg->payment_reference }}</span></div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center font-semibold text-white">
                        KES {{ number_format($reg->amount, 2) }}
                        @if($reg->quantity > 1)
                            <span class="block text-xs font-normal text-zinc-500">× {{ $reg->quantity }} seats</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($reg->payment_status === 'success')
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-emerald-500/20 text-emerald-400">Paid</span>
                        @elseif($reg->payment_status === 'failed')
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-500/20 text-red-400">Failed</span>
                        @else
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-orange-500/20 text-orange-400">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($reg->tickets->isEmpty())
                            <span class="text-xs text-zinc-500">No tickets issued yet.</span>
                        @else
                            <div class="space-y-2">
                                @foreach($reg->tickets as $ticket)
                                    <div class="flex items-center justify-between gap-2 text-xs">
                                        <div>
                                            <span class="text-zinc-200">{{ $ticket->attendee_name }}</span>
                                            <a href="{{ $ticket->verificationUrl() }}" target="_blank" class="block font-mono text-zinc-500 hover:text-emerald-400">{{ $ticket->code }}</a>
                                        </div>
                                        <form method="POST" action="{{ route('events.tickets.checkin', ['event' => $event->id, 'registration' => $reg->id, 'ticket' => $ticket->id]) }}">
                                            @csrf
                                            @if($ticket->isCheckedIn())
                                                <button type="submit" class="px-2 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 text-xs font-semibold">✓ Checked in</button>
                                            @else
                                                <button type="submit" class="px-2 py-1 rounded-lg bg-zinc-800 text-zinc-300 border border-zinc-700 text-xs font-semibold hover:border-emerald-500">Check in</button>
                                            @endif
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($reg->payment_status === 'success')
                            <form method="POST" action="{{ route('events.registrations.resend', ['event' => $event->id, 'registration' => $reg->id]) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                                    <i class="fas fa-paper-plane mr-1"></i> Send link
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-16 text-zinc-500">No registrations found for this event.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-center">
        {{ $registrations->appends(request()->query())->links() }}
    </div>
</div>
@endsection
