@extends('layouts.landing')
@section('title', 'Campaign News - My Leader Kenya')
@push('styles')
@include('account.partials.news-styles')
<style>.news-account-layout{grid-template-columns:280px minmax(0,1fr)}@media(max-width:900px){.news-account-layout{grid-template-columns:1fr}}</style>
@endpush
@section('content')
<div class="flag-stripe"></div>@include('components.frontend-nav')
<main class="news-account-shell"><div class="news-account-layout">
@include('components.aspirant-sidebar')
<section class="news-account-content">
<p class="news-kicker">Aspirant Dashboard</p><h1 class="news-title">Campaign news</h1><p class="news-subtitle">Articles associated with {{ $candidate->name }} and their publication status.</p>
<div class="news-actions"><a class="news-button primary" href="{{ route('aspirant.news.create') }}"><i class="fas fa-plus"></i>&nbsp; Submit News</a><a class="news-button" href="{{ route('aspirant.dashboard') }}">Dashboard</a></div>
@if(session('success'))<div class="news-alert">{{ session('success') }}</div>@endif
<div class="news-list">
@forelse($articles as $article)
<article class="news-row"><div><h2>{{ $article->title }}</h2><div class="news-meta">Submitted {{ $article->created_at->format('d M Y, H:i') }} by {{ $article->author?->name ?? 'Campaign team' }} &middot; {{ str($article->sentiment ?? 'neutral')->headline() }} tone</div>
@if($article->tags->isNotEmpty())<div class="news-tags">@foreach($article->tags as $tag)<span class="news-tag">{{ $tag->name }}</span>@endforeach</div>@endif</div>
<span class="news-status {{ $article->status === 'published' ? 'published' : 'pending' }}">{{ $article->status === 'published' ? 'Published' : 'Pending review' }}</span></article>
@empty<div class="news-empty"><p>No news has been submitted for this campaign yet.</p><a class="news-button primary" href="{{ route('aspirant.news.create') }}">Submit the first article</a></div>
@endforelse
</div>
@if($articles->hasPages())<nav class="news-pagination">@if($articles->onFirstPage())<span>Previous</span>@else<a class="news-button" href="{{ $articles->previousPageUrl() }}">Previous</a>@endif @if($articles->hasMorePages())<a class="news-button" href="{{ $articles->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif</nav>@endif
</section></div></main>
@endsection
