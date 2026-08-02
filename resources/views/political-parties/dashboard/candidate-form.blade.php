@extends('layouts.landing')
@section('title',($candidate->exists?'Edit':'Add').' Aspirant - '.$party->name)
@section('content')
@include('components.frontend-nav')
<main class="max-w-5xl mx-auto px-5 py-12 text-white">
<div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
<div class="flex justify-between mb-8">
<div>
<p class="text-emerald-400">{{ $party->name }}</p>
<h1 class="text-3xl font-bold">{{ $candidate->exists?'Edit':'Add' }} Aspirant</h1>
</div>
<a href="{{ route('party.dashboard') }}">Back</a>
</div>
@if(session('success'))<div class="border border-emerald-600 bg-emerald-950 p-4 rounded-xl mb-5">{{ session('success') }}</div>
@endif
 @if($errors->any())<div class="border border-red-700 bg-red-950 p-4 rounded-xl mb-5">{{ $errors->first() }}</div>
@endif

<form method="POST" enctype="multipart/form-data" action="{{ $candidate->exists?route('party.candidates.update',$candidate):route('party.candidates.store') }}" class="grid md:grid-cols-2 gap-5">

@csrf

 @if($candidate->exists)
@method('PUT')
@endif

@foreach(['name'=>'Full name','nick_name'=>'Nickname','phone'=>'Phone','email'=>'Email','country'=>'Country','county'=>'County','constituency'=>'Constituency','ward'=>'Ward'] as $field=>$label)<label>
<span class="text-sm text-zinc-400">{{ $label }}</span>
<input
    type="{{ $field === 'email' ? 'email' : 'text' }}"
    name="{{ $field }}"
    value="{{ old($field, $candidate->$field) }}"
    {{ $field === 'name' ? 'required' : '' }}
    class="mt-2 w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3"
>
</label>
@endforeach

<label>
<span class="text-sm text-zinc-400">Position</span>
<select name="position_id" required class="mt-2 w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3">
@foreach($positions as $position)
<option value="{{ $position->id }}" @selected(old('position_id', $candidate->position_id) == $position->id)>
{{ $position->name }}
</option>
@endforeach
</select>
</label>
<div>
</div>
@php
    $socialFields = [
        'facebook_url' => 'Facebook',
        'x_url' => 'X',
        'instagram_url' => 'Instagram',
        'tiktok_url' => 'TikTok',
        'youtube_url' => 'YouTube',
        'whatsapp_group_url' => 'WhatsApp Group',
        'campaign_video_url' => 'Campaign Video (YouTube)',
        'campaign_song_url' => 'Campaign Song (YouTube)',
    ];
@endphp
@foreach($socialFields as $field => $label)
<label>
<span class="text-sm text-zinc-400">{{ $label }}</span>
<input type="url" name="{{ $field }}" value="{{ old($field,$candidate->$field) }}" class="mt-2 w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3">
</label>
@endforeach

@foreach(['profile_picture'=>'Profile picture','cover_photo'=>'Cover photo','campaign_poster'=>'Campaign poster','campaign_skiza_audio'=>'Campaign Skiza audio'] as $field=>$label)<label>
<span class="text-sm text-zinc-400">{{ $label }}</span>
<input type="file" name="{{ $field }}" class="mt-2 w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3">
</label>
@endforeach

<div class="md:col-span-2 border-t border-zinc-700 pt-5">
<h2 class="text-xl font-semibold mb-4">Support groups</h2>
<div class="grid gap-4">@for($i=0;$i<3;$i++) @php($contact=$candidate->supportContacts[$i]??null)<div class="grid md:grid-cols-4 gap-3">
<select name="support_contacts[{{ $i }}][support_group_type_id]" class="bg-zinc-800 border border-zinc-700 rounded-xl p-3">
<option value="">Group type</option>
@foreach($supportGroupTypes as $type)
<option
    value="{{ $type->id }}"
    @selected(old("support_contacts.$i.support_group_type_id", $contact?->support_group_type_id) == $type->id)
>
    {{ $type->name }}
</option>
@endforeach
</select>
<input name="support_contacts[{{ $i }}][name]" value="{{ old("support_contacts.$i.name",$contact?->name) }}" placeholder="Contact name" class="bg-zinc-800 border border-zinc-700 rounded-xl p-3">
<input
    type="email"
    name="support_contacts[{{ $i }}][email]"
    value="{{ old("support_contacts.$i.email", $contact?->email) }}"
    placeholder="Email"
    class="bg-zinc-800 border border-zinc-700 rounded-xl p-3"
>
<input name="support_contacts[{{ $i }}][phone]" value="{{ old("support_contacts.$i.phone",$contact?->phone) }}" placeholder="Phone" class="bg-zinc-800 border border-zinc-700 rounded-xl p-3">
</div>
@endfor
</div>
</div>
<label class="md:col-span-2">
<span class="text-sm text-zinc-400">Biography / campaign information</span>
<textarea name="about" rows="8" class="mt-2 w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3">{{ old('about',$candidate->about) }}</textarea>
</label>
<button class="md:col-span-2 bg-emerald-600 rounded-xl p-4 font-bold">{{ $candidate->exists?'Save changes':'Add for approval' }}</button>
</form>
</div>
</main>
@endsection
