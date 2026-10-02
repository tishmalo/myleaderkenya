@extends('layouts.landing')
@section('title', 'My Links - My Leader Kenya')
@push('styles')@include('account.partials.news-styles')@endpush
@section('content')
<div class="flag-stripe"></div>@include('components.frontend-nav')
<main class="news-account-shell"><div class="news-account-layout">
@include('components.my-account-sidebar')
<section class="news-account-content">
<p class="news-kicker">My Account</p><h1 class="news-title">My links &amp; pages</h1><p class="news-subtitle">Track every link you have submitted and its approval status. Approved links appear on the matching aspirant and county pages.</p>
<div class="news-actions"><a class="news-button primary" href="{{ route('account.links.create') }}"><i class="fas fa-plus"></i>&nbsp; Add Link/Page</a></div>
@if(session('success'))<div class="news-alert">{{ session('success') }}</div>@endif
<div class="news-list">
@forelse($links as $link)
<article class="news-row"><div>
<h2>{{ $link->display_title }}{{ $link->candidate ? ' &bull; '.$link->candidate->name : '' }}</h2>
<div class="news-meta">{{ $link->platform_label }} &bull; {{ $link->county?->name }}@if($link->constituency) , {{ $link->constituency->name }}@endif @if($link->ward) , {{ $link->ward->name }}@endif &bull; Submitted {{ $link->created_at->format('d M Y, H:i') }}</div>
<div class="news-meta"><a href="{{ $link->url }}" target="_blank" rel="noopener nofollow">{{ Str::limit($link->url, 60) }}</a>@if($link->followers) &bull; {{ number_format($link->followers) }} followers @endif</div>
@if($link->comment)<div class="news-meta">{{ $link->comment }}</div>@endif
</div>
<span class="news-status {{ $link->approval_status === 'approved' ? 'published' : 'pending' }}">{{ match ($link->approval_status) { 'approved' => 'Published', 'rejected' => 'Rejected', default => 'Pending review' } }}</span></article>
@empty<div class="news-empty"><p>You have not submitted any links yet.</p><a class="news-button primary" href="{{ route('account.links.create') }}">Submit your first link</a></div>
@endforelse
</div>
@if($links->hasPages())<nav class="news-pagination">@if($links->onFirstPage())<span>Previous</span>@else<a class="news-button" href="{{ $links->previousPageUrl() }}">Previous</a>@endif @if($links->hasMorePages())<a class="news-button" href="{{ $links->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif</nav>@endif
</section></div></main>
@endsection