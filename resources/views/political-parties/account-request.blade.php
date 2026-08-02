@extends('layouts.landing')
@section('title','Request '.$politicalParty->name.' Dashboard Access')
@section('content')
@include('components.frontend-nav')
<main class="max-w-3xl mx-auto px-5 py-16 text-white">
<div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
<h1 class="text-3xl font-bold mb-2">Request party dashboard access</h1>
<p class="text-zinc-400 mb-8">{{ $politicalParty->name }} officials must provide proof of authorization. Access activates after admin review.</p>
@if($errors->any())<div class="bg-red-950 border border-red-800 p-4 rounded-xl mb-5">{{ $errors->first() }}</div>
@endif

<form method="POST" enctype="multipart/form-data" action="{{ route('parties.access.store',$politicalParty) }}" class="grid md:grid-cols-2 gap-5">

@csrf


@foreach(['name'=>'Full name','email'=>'Official email','phone'=>'Phone','party_title'=>'Party title / role'] as $name=>$label)<label class="block">
<span class="text-sm text-zinc-300">{{ $label }}</span>
<input name="{{ $name }}" value="{{ old($name) }}" required class="mt-2 w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3">
</label>
@endforeach

<label class="block">
<span class="text-sm text-zinc-300">Password</span>
<input type="password" name="password" required class="mt-2 w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3">
</label>
<label class="block">
<span class="text-sm text-zinc-300">Confirm password</span>
<input type="password" name="password_confirmation" required class="mt-2 w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3">
</label>
<label class="block md:col-span-2">
<span class="text-sm text-zinc-300">Authorization document (PDF/JPG/PNG, max 5MB)</span>
<input type="file" name="authorization_document" required class="mt-2 w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3">
</label>
<button class="md:col-span-2 bg-emerald-600 hover:bg-emerald-500 rounded-xl p-4 font-bold">Submit for admin review</button>
</form>
</div>
</main>
@endsection
