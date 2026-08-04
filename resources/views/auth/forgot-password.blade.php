<x-guest-layout>
<div class="auth-heading"><span class="auth-icon">&#9993;</span><h1>Reset your password</h1><p>Enter the email address linked to your account. We will send you a secure reset link.</p></div>
@if(session('status'))<div class="auth-alert">{{ session('status') }}</div>@endif
<form method="POST" action="{{ route('password.email') }}" class="auth-form">@csrf
<div class="auth-field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email', request('email')) }}" placeholder="you@example.com" required autofocus autocomplete="email">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
<button type="submit" class="auth-submit">Send password reset link</button>
<div class="auth-secondary">Remembered your password? <a href="{{ route('landing') }}">Return to login</a></div>
</form></x-guest-layout>
