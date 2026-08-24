@extends('layouts.landing')
@section('title', 'Submit Event - My Leader Kenya')
@push('styles')
@include('account.partials.news-styles')
<style>.news-account-layout{grid-template-columns:280px minmax(0,1fr)}@media(max-width:900px){.news-account-layout{grid-template-columns:1fr}}</style>
@endpush
@section('content')
<div class="flag-stripe"></div>@include('components.frontend-nav')
<main class="news-account-shell"><div class="news-account-layout">
@include('components.my-account-sidebar')
<section class="news-account-content">
<p class="news-kicker">My Account</p><h1 class="news-title">Submit an event</h1><p class="news-subtitle">Your event stays private until an administrator approves it.</p>
<div class="news-actions"><a class="news-button" href="{{ route('account.events.index') }}">&larr; My Events</a></div>
@if($errors->any())<div class="news-alert" style="border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.1);color:#fecaca">{{ $errors->first() }}</div>@endif
<form class="news-form" method="POST" action="{{ route('account.events.store') }}" enctype="multipart/form-data">@csrf
<div class="news-form-grid">
<div class="news-field full"><label for="title">Event title *</label><input id="title" name="title" value="{{ old('title') }}" maxlength="255" required placeholder="e.g. Youth Town Hall on County Development">@error('title')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field full"><label for="description">Description *</label><textarea id="description" name="description" rows="6" maxlength="5000" required placeholder="Describe the purpose, agenda and speakers of the event...">{{ old('description') }}</textarea>@error('description')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="date">Date &amp; time *</label><input id="date" type="datetime-local" name="date" value="{{ old('date') }}" required><p class="news-help">Must be a future date and time.</p>@error('date')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="location">Location *</label><input id="location" name="location" value="{{ old('location') }}" maxlength="255" required placeholder="e.g. KICC, Nairobi or Online">@error('location')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="poster">Event poster / image</label><input id="poster" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,.gif,image/*"><p class="news-help">Optional. JPG, PNG, WEBP or GIF, maximum 5 MB.</p>@error('poster')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="promo_video">Promo video (YouTube)</label><input id="promo_video" type="url" name="promo_video" value="{{ old('promo_video') }}" placeholder="https://www.youtube.com/watch?v=..."><p class="news-help">Optional. Paste a YouTube video link.</p>@error('promo_video')<p class="news-error">{{ $message }}</p>@enderror</div>
</div><button class="news-submit" type="submit">Submit for administrator review</button>
</form>
</section></div></main>
@endsection
