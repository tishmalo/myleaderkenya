@extends('layouts.app')

@section('page_title', 'reCAPTCHA Settings')

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

                <h1 class="text-2xl font-semibold text-white">Google reCAPTCHA Settings</h1>
                <p class="text-sm text-zinc-500 mt-1">Used to protect the public "Request Feature" campaign tool form against spam. Uses invisible reCAPTCHA v3.</p>

                @if($recaptchaSiteKey && $recaptchaSecretKey)
                    <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> reCAPTCHA is enabled
                    </div>
                @else
                    <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-400 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span> reCAPTCHA is disabled
                    </div>
                    <p class="text-xs text-zinc-500 mt-2">The form still works (honeypot + time checks stay active), but Google reCAPTCHA verification is off. Add both keys to enable it.</p>
                @endif

                <form action="{{ route('admin.recaptcha.update') }}" method="POST" class="space-y-6 mt-6">
                    @csrf

                    <div>
                        <label for="recaptcha_site_key" class="block text-sm font-medium text-zinc-300">Site Key</label>
                        <input type="text" id="recaptcha_site_key" name="recaptcha_site_key" value="{{ old('recaptcha_site_key', $recaptchaSiteKey) }}" placeholder="6Lxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                        @error('recaptcha_site_key')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="recaptcha_secret_key" class="block text-sm font-medium text-zinc-300">Secret Key</label>
                        <input type="password" id="recaptcha_secret_key" name="recaptcha_secret_key" value="{{ old('recaptcha_secret_key', $recaptchaSecretKey) }}" placeholder="6Lxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" autocomplete="off" class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                        @error('recaptcha_secret_key')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-xs text-zinc-500 leading-relaxed">
                        <p class="font-semibold text-zinc-400 mb-1">How to get your keys</p>
                        <p>1. Visit the <a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener" class="text-emerald-400 hover:underline">Google reCAPTCHA admin console</a>.</p>
                        <p>2. Register a new site, choose <strong>reCAPTCHA v3</strong>, and add your domain.</p>
                        <p>3. Copy the Site Key and Secret Key here, then save. The invisible reCAPTCHA is enabled immediately.</p>
                        <p class="mt-2 text-zinc-400">Leave both fields blank to disable reCAPTCHA entirely.</p>
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
