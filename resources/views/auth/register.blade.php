<x-guest-layout>
@section('title', 'Create Account')
<div class="auth-heading"><span class="auth-icon">&#128100;</span><h1>Create your account</h1><p>Join My Leader Kenya to vote in polls for your area.</p></div>
<form method="POST" action="{{ route('register') }}" class="auth-form" data-register-form>@csrf
<div class="auth-field"><label for="name">Full name <span class="auth-required" aria-hidden="true">*</span></label><input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">@error('name')<p class="auth-error">{{ $message }}</p>@enderror</div>
<div class="auth-row">
<div class="auth-field"><label for="phone">Phone <span class="auth-required" aria-hidden="true">*</span></label><input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel">@error('phone')<p class="auth-error">{{ $message }}</p>@enderror</div>
<div class="auth-field"><label for="email">Email address <span class="auth-required" aria-hidden="true">*</span></label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"><p class="auth-match" data-email-status hidden></p>@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
</div>
<div class="auth-field"><label for="password">Password <span class="auth-required" aria-hidden="true">*</span></label><div class="auth-input-wrap"><input id="password" class="has-toggle" type="password" name="password" required autocomplete="new-password"><button class="auth-toggle" type="button" data-toggle="password">SHOW</button></div>@error('password')<p class="auth-error">{{ $message }}</p>@enderror</div>
<div class="auth-field"><label for="password_confirmation">Confirm password <span class="auth-required" aria-hidden="true">*</span></label><div class="auth-input-wrap"><input id="password_confirmation" class="has-toggle" type="password" name="password_confirmation" required autocomplete="new-password"><button class="auth-toggle" type="button" data-toggle="password_confirmation">SHOW</button></div><p class="auth-match" data-match hidden></p></div>
<button type="submit" class="auth-submit">Create My Account</button>
<p class="auth-secondary">Already registered? <a href="{{ route('login') }}">Sign in here</a></p>
</form>
<style>
.auth-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:520px){.auth-row{grid-template-columns:1fr}}
.auth-required{color:#f87171}
.auth-secondary{margin:4px 0 0;text-align:center;color:#a1a1aa;font-size:14px}
.auth-secondary a{color:#34d399;font-weight:700;text-decoration:none}
.auth-secondary a:hover{text-decoration:underline}
</style>
<script>
(function(){const f=document.querySelector('[data-register-form]');if(!f)return;const p=f.querySelector('#password'),c=f.querySelector('#password_confirmation'),m=f.querySelector('[data-match]'),s=f.querySelector('[type="submit"]'),email=f.querySelector('#email'),es=f.querySelector('[data-email-status]');
f.querySelectorAll('[data-toggle]').forEach(function(b){b.addEventListener('click',function(){const i=f.querySelector('#'+b.dataset.toggle),show=i.type==='password';i.type=show?'text':'password';b.textContent=show?'HIDE':'SHOW'})});
function check(){if(!c.value){m.hidden=true;s.disabled=false;return}const ok=p.value===c.value;m.hidden=false;m.className='auth-match'+(ok?' ok':'');m.textContent=ok?'Passwords match.':'Passwords do not match yet.';s.disabled=!ok}
p.addEventListener('input',check);c.addEventListener('input',check);
let timer;const availabilityUrl="{{ route('aspirants.email-availability') }}";email.addEventListener('input',function(){clearTimeout(timer);email.setCustomValidity('');const value=email.value.trim().toLowerCase();if(!value||!email.validity.valid){es.hidden=true;return}es.hidden=false;es.className='auth-match';es.textContent='Checking email...';timer=setTimeout(async function(){try{const token=f.querySelector('input[name="_token"]').value;const response=await fetch(availabilityUrl,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token},body:JSON.stringify({email:value})});const result=await response.json();if(email.value.trim().toLowerCase()!==value)return;email.setCustomValidity(result.available?'':result.message);es.className='auth-match'+(result.available?' ok':'');es.textContent=result.message}catch(_){es.hidden=true}},450)});
})();
</script></x-guest-layout>
