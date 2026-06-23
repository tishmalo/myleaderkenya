@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('dashboard.messages') }}" 
           class="text-zinc-400 hover:text-white">
            ← Back to Messages
        </a>
        <h1 class="text-3xl font-semibold text-white">
            👥 {{ $group->name }}
        </h1>
    </div>

    @if($group->description)
        <p class="text-zinc-400 mb-8">{{ $group->description }}</p>
    @endif

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <!-- Chat Header -->
        <div class="bg-zinc-950 px-6 py-4 border-b border-zinc-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-emerald-400">
                    <i class="fas fa-users"></i>
                </span>
                <span class="font-medium">{{ $group->name }}</span>
            </div>
            <div class="text-sm text-zinc-500">
                Invite Code: <span class="font-mono bg-zinc-800 px-3 py-1 rounded-lg">{{ $group->invite_code }}</span>
            </div>
        </div>

        <!-- Messages -->
        <div class="h-[600px] overflow-y-auto p-6 space-y-6" id="chat-container">
            @forelse($messages as $msg)
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-9 h-9 bg-zinc-700 rounded-full flex items-center justify-center text-sm font-medium">
                            {{ strtoupper(substr($msg->username, 0, 1)) }}
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-white">{{ $msg->username }}</span>
                            <span class="text-xs text-zinc-500">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-zinc-300 mt-1">{{ $msg->message }}</p>
                        @if($msg->latitude && $msg->longitude)
                            <p class="text-xs text-zinc-500 mt-2 font-mono">
                                📍 {{ number_format($msg->latitude, 5) }}, {{ number_format($msg->longitude, 5) }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-zinc-500 py-20">
                    No messages yet. Be the first to send one!
                </div>
            @endforelse
        </div>

        <!-- Message Input -->
        <form method="POST" action="{{ route('groups.messages.store', $group) }}" class="border-t border-zinc-800 p-6">
            @csrf
            <div class="flex gap-3">
                <input type="text" 
                       name="message"
                       required
                       maxlength="500"
                       placeholder="Type your message here..."
                       class="flex-1 bg-zinc-800 border border-zinc-700 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-violet-500">
                
                <button type="submit"
                        class="bg-violet-600 hover:bg-violet-700 px-8 rounded-2xl font-medium">
                    Send
                </button>
            </div>
        </form>
    </div>

    <div class="mt-4 text-center text-xs text-zinc-500">
        Share this invite code with others: <strong class="font-mono">{{ $group->invite_code }}</strong>
    </div>
</div>
@endsection