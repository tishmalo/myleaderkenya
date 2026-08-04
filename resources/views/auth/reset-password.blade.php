<x-guest-layout>
<div class="auth-heading"><span class="auth-icon">&#128274;</span><h1>Choose a new password</h1><p>Use a strong password you have not used elsewhere.</p></div>
<form method="POST" action="{{ route('password.store') }}" class="auth-form" data-reset-form>@csrf
<input type="hidden" name="token" value="{{ $request->route('token') }}">
<div class="auth-field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
<div class="auth-field"><label for="password">New password</label><div class="auth-input-wrap"><input id="password" class="has-toggle" type="password" name="password" required autocomplete="new-password"><button class="auth-toggle" type="button" data-toggle="password">SHOW</button></div>@error('password')<p class="auth-error">{{ $message }}</p>@enderror</div>
<div class="auth-field"><label for="password_confirmation">Confirm new password</label><div class="auth-input-wrap"><input id="password_confirmation" class="has-toggle" type="password" name="password_confirmation" required autocomplete="new-password"><button class="auth-toggle" type="button" data-toggle="password_confirmation">SHOW</button></div><p class="auth-match" data-match hidden></p>@error('password_confirmation')<p class="auth-error">{{ $message }}</p>@enderror</div>
<button type="submit" class="auth-submit">Reset password</button>
</form>
<script>
(function(){const f=document.querySelector('[data-reset-form]');if(!f)return;const p=f.querySelector('#password'),c=f.querySelector('#password_confirmation'),m=f.querySelector('[data-match]'),s=f.querySelector('[type="submit"]');f.querySelectorAll('[data-toggle]').forEach(function(b){b.addEventListener('click',function(){const i=f.querySelector('#'+b.dataset.toggle),show=i.type==='password';i.type=show?'text':'password';b.textContent=show?'HIDE':'SHOW'})});function check(){if(!c.value){m.hidden=true;s.disabled=false;return}const ok=p.value===c.value;m.hidden=false;m.className='auth-match'+(ok?' ok':'');m.textContent=ok?'Passwords match.':'Passwords do not match yet.';s.disabled=!ok}p.addEventListener('input',check);c.addEventListener('input',check)})();
</script></x-guest-layout>
