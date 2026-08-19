@extends('layouts.app')

@section('page_title', 'Email Notifications')

@section('content')
<div class="py-6">
    <div class="max-w-5xl mx-auto">
        @if(session('success'))
            <div class="mb-6 p-4 text-sm font-medium text-emerald-400 bg-emerald-500/10 rounded-2xl border border-emerald-500/20">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="mb-6 p-4 text-sm font-medium text-orange-400 bg-orange-500/10 rounded-2xl border border-orange-500/20">{{ session('warning') }}</div>
        @endif

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-white">Email Notifications</h1>
            <p class="text-sm text-zinc-500 mt-1">Manage the emails the system sends. Disabled notifications are skipped entirely.</p>
        </div>

        <div class="space-y-4">
            @foreach($notifications as $notification)
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-semibold text-white">{{ $notification['label'] }}</h2>
                                @if($notification['enabled'])
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-500/20 text-emerald-400">Enabled</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-zinc-800 text-zinc-500">Disabled</span>
                                @endif
                            </div>
                            <p class="text-sm text-zinc-500 mt-1">{{ $notification['description'] }}</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach($notification['placeholders'] as $placeholder)
                                    <span class="px-2 py-0.5 text-xs font-mono bg-zinc-800 text-emerald-400 rounded-lg border border-zinc-700">{{ $placeholder }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <form method="POST" action="{{ route('notification-emails.toggle', $notification['key']) }}">
                                @csrf
                                <input type="hidden" name="enabled" value="{{ $notification['enabled'] ? '0' : '1' }}">
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold border {{ $notification['enabled'] ? 'bg-zinc-800 text-zinc-300 border-zinc-700 hover:border-red-500' : 'bg-emerald-600 text-white border-transparent hover:bg-emerald-700' }}">
                                    {{ $notification['enabled'] ? 'Disable' : 'Enable' }}
                                </button>
                            </form>

                            <a href="{{ route('notification-emails.edit', $notification['key']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-800 text-zinc-300 border border-zinc-700 hover:border-emerald-500">Edit</a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('notification-emails.test', $notification['key']) }}" class="mt-4 flex items-end gap-3 border-t border-zinc-800 pt-4">
                        @csrf
                        <div class="flex-1">
                            <label for="email-{{ $notification['key'] }}" class="block text-xs font-medium text-zinc-400">Send test email to</label>
                            <input type="email" id="email-{{ $notification['key'] }}" name="email" required placeholder="you@example.com" class="mt-1 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3">
                        </div>
                        <button type="submit" class="px-4 py-2.5 rounded-2xl bg-zinc-800 text-zinc-300 border border-zinc-700 text-sm font-medium hover:border-emerald-500">
                            <i class="fas fa-paper-plane mr-1"></i> Send test
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
