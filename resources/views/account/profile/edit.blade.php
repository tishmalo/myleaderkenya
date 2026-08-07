@extends('layouts.landing')
@section('title', 'My Profile - My Leader Kenya')

@push('styles')
<style>
.profile-shell{min-height:100vh;background:#09090b;color:#fff;padding:18px}.profile-layout{display:grid;grid-template-columns:270px minmax(0,1fr);gap:18px;max-width:1440px;margin:auto}.account-sidebar{position:sticky;top:18px;display:flex;max-height:calc(100vh - 36px);min-height:calc(100vh - 36px);flex-direction:column;border:1px solid #27272a;border-radius:18px;background:#111113;padding:18px}.account-sidebar-brand{display:flex;align-items:center;gap:12px;border-bottom:1px solid #27272a;padding:4px 4px 18px;color:#fff;text-decoration:none}.account-sidebar-logo{display:grid;width:43px;height:43px;place-items:center;border-radius:13px;background:#073b2e;color:#34d399}.account-sidebar-brand small,.account-sidebar-brand strong{display:block}.account-sidebar-brand small{color:#71717a;font-size:10px;font-weight:900;text-transform:uppercase}.account-sidebar-brand strong{font-size:21px}.account-sidebar-nav{display:grid;gap:7px;margin-top:18px}.account-sidebar-link,.account-sidebar-logout{display:flex;min-height:45px;align-items:center;gap:12px;border:1px solid transparent;border-radius:11px;padding:0 13px;color:#a1a1aa;font-size:13px;font-weight:800;text-decoration:none}.account-sidebar-link:hover,.account-sidebar-link.is-active{border-color:#3f3f46;background:#202023;color:#fff}.account-sidebar-link.is-primary{background:#059669;color:#fff}.account-sidebar-footer{margin-top:auto;border-top:1px solid #27272a;padding-top:16px}.account-sidebar-logout{width:100%;background:#2a1113;color:#fca5a5}.profile-main{border:1px solid #202024;border-radius:18px;background:#0d0d0f;padding:clamp(22px,4vw,46px)}.profile-kicker{color:#34d399;font-size:11px;font-weight:900;letter-spacing:.2em;text-transform:uppercase}.profile-title{margin:8px 0;font-size:clamp(30px,4vw,46px)}.profile-copy{color:#a1a1aa}.profile-alert{margin-top:20px;border:1px solid #92400e;border-radius:12px;background:#451a03;padding:14px;color:#fde68a}.profile-success{border-color:#065f46;background:#052e27;color:#a7f3d0}.profile-section{margin-top:28px;border-top:1px solid #27272a;padding-top:24px}.profile-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.profile-field.full{grid-column:1/-1}.profile-field label{display:block;margin-bottom:8px;color:#d4d4d8;font-size:13px;font-weight:800}.profile-field input,.profile-field select{width:100%;min-height:48px;border:1px solid #3f3f46;border-radius:12px;background:#17171a;padding:0 14px;color:#fff}.profile-error{margin-top:5px;color:#f87171;font-size:12px}.profile-voter{display:flex!important;align-items:center;gap:10px;border:1px solid #3f3f46;border-radius:12px;padding:14px}.profile-voter input{width:auto}.profile-submit{width:100%;min-height:52px;margin-top:28px;border:0;border-radius:12px;background:#059669;color:#fff;font-weight:900}@media(max-width:860px){.profile-layout{grid-template-columns:1fr}.account-sidebar{position:static;min-height:auto}.profile-grid{grid-template-columns:1fr}.profile-field.full{grid-column:auto}}
</style>
@endpush

@section('content')
<section class="profile-shell"><div class="profile-layout">
@include('components.my-account-sidebar')
<main class="profile-main">
<div class="profile-kicker">Account verification</div>
<h1 class="profile-title">{{ $profileComplete ? 'My Profile' : 'Complete Your Profile' }}</h1>
<p class="profile-copy">Confirm all your identity and voter location details before using the dashboard toolkit.</p>
@if(!$profileComplete || session('warning'))<div class="profile-alert">{{ session('warning', 'Complete every required field before accessing dashboard tools.') }}</div>@endif
@if(session('success'))<div class="profile-alert profile-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="profile-alert">Please correct the highlighted details.</div>@endif

<form method="POST" action="{{ route('account.profile.update') }}">@csrf @method('PUT')
<section class="profile-section"><h2>Account details</h2><div class="profile-grid">
@php
$fields = [
    ['name','Full Name','text'],
    ['username','Username','text'],
    ['email','Email Address','email'],
    ['phone','Phone Number','tel'],
    ['id_number','National ID / Identification Number','text'],
    ['country_of_residence','Country of Residence','text'],
    ['year_of_birth','Year of Birth','number'],
];
@endphp
@foreach($fields as [$field,$label,$type])
<div class="profile-field"><label for="profile-{{ $field }}">{{ $label }} *</label>
<input id="profile-{{ $field }}" type="{{ $type }}" name="{{ $field }}" value="{{ old($field, auth()->user()->{$field} ?: ($field === 'country_of_residence' ? 'Kenya' : '')) }}" required @if($field==='year_of_birth') min="1900" max="{{ date('Y') }}" @endif>
@error($field)<div class="profile-error">{{ $message }}</div>@enderror</div>
@endforeach
<div class="profile-field"><label for="profile-gender">Gender *</label><select id="profile-gender" name="gender" required><option value="">Select Gender</option>
@foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $value=>$label)<option value="{{ $value }}" @selected(old('gender',auth()->user()->gender)===$value)>{{ $label }}</option>@endforeach
</select>@error('gender')<div class="profile-error">{{ $message }}</div>@enderror</div>
</div></section>

<section class="profile-section"><h2>Voter location</h2><div class="profile-grid">
@foreach([
 ['county','County',$counties],
 ['constituency','Constituency',$constituencies],
 ['ward','Ward',$wards],
 ['polling_station','Polling Station',$pollingStations],
] as [$field,$label,$options])
<div class="profile-field"><label for="profile-{{ $field }}">{{ $label }} *</label><select id="profile-{{ $field }}" name="{{ $field }}" required><option value="">Select {{ $label }}</option>
@foreach($options as $option)<option value="{{ $option }}" @selected(old($field,auth()->user()->{$field})===$option)>{{ $option }}</option>@endforeach
</select>@error($field)<div class="profile-error">{{ $message }}</div>@enderror</div>
@endforeach
<div class="profile-field full"><input type="hidden" name="is_voter" value="0"><label class="profile-voter"><input type="checkbox" name="is_voter" value="1" @checked((bool)old('is_voter',auth()->user()->is_voter))><span>I am a registered voter</span></label></div>
</div></section>
<button class="profile-submit">{{ $profileComplete ? 'Save Profile Changes' : 'Verify and Continue to Dashboard' }}</button>
</form></main></div></section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
const county=document.getElementById('profile-county'),constituency=document.getElementById('profile-constituency'),ward=document.getElementById('profile-ward'),station=document.getElementById('profile-polling_station');
const reset=(el,label)=>{el.innerHTML='';el.add(new Option(label,''));};
const load=async(el,url,label)=>{reset(el,'Loading...');el.disabled=true;try{const response=await fetch(url,{headers:{Accept:'application/json'}});if(!response.ok)throw new Error();const values=await response.json();reset(el,label);values.forEach(value=>{const text=typeof value==='string'?value:(value.office||value.name);el.add(new Option(text,text));});}catch(e){reset(el,'Unable to load options');}finally{el.disabled=false;}};
county.addEventListener('change',async()=>{reset(constituency,'Select Constituency');reset(ward,'Select Ward');reset(station,'Select Polling Station');if(county.value)await load(constituency,'/api/locations/constituencies/by-county?county='+encodeURIComponent(county.value),'Select Constituency');});
constituency.addEventListener('change',async()=>{reset(ward,'Select Ward');reset(station,'Select Polling Station');if(constituency.value)await load(ward,'/api/locations/wards/by-constituency?constituency='+encodeURIComponent(constituency.value),'Select Ward');});
ward.addEventListener('change',async()=>{reset(station,'Select Polling Station');if(ward.value)await load(station,'/api/locations/polling-stations/by-ward?ward='+encodeURIComponent(ward.value),'Select Polling Station');});
});
</script>
@endpush