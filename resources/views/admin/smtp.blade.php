@extends('layouts.app')

@section('page_title', 'SMTP Settings')

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

                <h1 class="text-2xl font-semibold text-white">SMTP Settings</h1>
                <p class="text-sm text-zinc-500 mt-1">Configure the outgoing mail server. Values are written to the .env file.</p>

                <form action="{{ route('admin.smtp.update') }}" method="POST" class="space-y-6 mt-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="mail_host" class="block text-sm font-medium text-zinc-300">Host</label>
                            <input type="text" id="mail_host" name="mail_host" value="{{ old('mail_host', config('mail.mailers.smtp.host')) }}" required class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                            @error('mail_host')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="mail_port" class="block text-sm font-medium text-zinc-300">Port</label>
                            <input type="number" id="mail_port" name="mail_port" value="{{ old('mail_port', config('mail.mailers.smtp.port')) }}" required class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                            @error('mail_port')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="mail_username" class="block text-sm font-medium text-zinc-300">Username</label>
                            <input type="text" id="mail_username" name="mail_username" value="{{ old('mail_username', config('mail.mailers.smtp.username')) }}" required class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                            @error('mail_username')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="mail_password" class="block text-sm font-medium text-zinc-300">Password</label>
                            <input type="password" id="mail_password" name="mail_password" value="{{ old('mail_password', config('mail.mailers.smtp.password')) }}" required class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                            @error('mail_password')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="mail_encryption" class="block text-sm font-medium text-zinc-300">Encryption</label>
                            <select id="mail_encryption" name="mail_encryption" class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                                <option value="">None</option>
                                <option value="tls" {{ old('mail_encryption', config('mail.mailers.smtp.encryption')) === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('mail_encryption', config('mail.mailers.smtp.encryption')) === 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                            @error('mail_encryption')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="mail_from_address" class="block text-sm font-medium text-zinc-300">From Address</label>
                            <input type="email" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', config('mail.from.address')) }}" required class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                            @error('mail_from_address')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="mail_from_name" class="block text-sm font-medium text-zinc-300">From Name</label>
                        <input type="text" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', config('mail.from.name')) }}" required class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                        @error('mail_from_name')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-medium text-white hover:bg-emerald-700">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
