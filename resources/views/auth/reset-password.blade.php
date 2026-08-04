<x-guest-layout>
<div class="mb-7 text-center"><h1 class="text-2xl font-extrabold text-white">Choose a new password</h1><p class="mt-2 text-sm leading-6 text-zinc-400">Use a strong password you have not used elsewhere.</p></div>
<form method="POST" action="{{ route('password.store') }}" class="space-y-5">@csrf<input type="hidden" name="token" value="{{ $request->route('token') }}">
<div><label for="email" class="mb-2 block text-sm font-semibold text-zinc-300">Email address</label><input id="email" class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-white outline-none focus:border-emerald-500" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">@error('email')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror</div>
@foreach ([['password', 'New password'], ['password_confirmation', 'Confirm new password']] as [$field, $label])
<div><label for="{{ $field }}" class="mb-2 block text-sm font-semibold text-zinc-300">{{ $label }}</label><input id="{{ $field }}" class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-white outline-none focus:border-emerald-500" type="password" name="{{ $field }}" required autocomplete="new-password">@error($field)<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror</div>
@endforeach
<button type="submit" class="w-full rounded-2xl bg-emerald-600 px-5 py-3.5 text-sm font-bold uppercase tracking-wider text-white transition hover:bg-emerald-500">Reset password</button></form></x-guest-layout>
