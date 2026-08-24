@extends('layouts.landing')
@section('title', 'My Events - My Leader Kenya')
@push('styles')
@include('account.partials.news-styles')
<style>.news-account-layout{grid-template-columns:280px minmax(0,1fr)}@media(max-width:900px){.news-account-layout{grid-template-columns:1fr}}</style>
@endpush
@section('content')
<div class="flag-stripe"></div>@include('components.frontend-nav')
<main class="news-account-shell"><div class="news-account-layout">
@include('components.aspirant-sidebar')
<section class="news-account-content">
<p class="news-kicker">Aspirant Dashboard</p><h1 class="news-title">My events</h1><p class="news-subtitle">Events you have submitted. Each one remains private until an administrator approves it.</p>
<div class="news-actions"><a class="news-button primary" href="{{ route('aspirant.events.create') }}"><i class="fas fa-plus"></i>&nbsp; Submit Event</a><a class="news-button" href="{{ route('aspirant.dashboard') }}">Dashboard</a></div>
@if(session('success'))<div class="news-alert">{{ session('success') }}</div>@endif
<div class="news-list">
@forelse($events as $event)
<article class="news-row"><div><h2>{{ $event->title }}</h2><div class="news-meta"><i class="far fa-calendar-alt"></i> {{ $event->date->format('d M Y, h:i A') }} &middot; <i class="fas fa-map-marker-alt"></i> {{ $event->location }} &middot; {{ (float) $event->price > 0 ? 'KES '.number_format($event->price, 2) : 'Free' }} &middot; {{ $event->registrations_count }} {{ Str::plural('registration', $event->registrations_count) }}</div></div>
@if($event->approval_status === 'approved')<span class="news-status published">Approved</span>
@elseif($event->approval_status === 'rejected')<span class="news-status pending" style="border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.1);color:#fca5a5">Rejected</span>
@else<span class="news-status pending">Pending review</span>@endif</article>
@empty<div class="news-empty"><p>You have not submitted any events yet.</p><a class="news-button primary" href="{{ route('aspirant.events.create') }}">Submit your first event</a></div>
@endforelse
</div>
@if($events->hasPages())<nav class="news-pagination">@if($events->onFirstPage())<span>Previous</span>@else<a class="news-button" href="{{ $events->previousPageUrl() }}">Previous</a>@endif @if($events->hasMorePages())<a class="news-button" href="{{ $events->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif</nav>@endif
</section></div></main>
@endsection
