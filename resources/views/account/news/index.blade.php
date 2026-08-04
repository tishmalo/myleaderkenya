@extends('layouts.landing')
@section('title', 'My News - My Leader Kenya')
@push('styles')@include('account.partials.news-styles')@endpush
@section('content')
<div class="flag-stripe"></div>@include('components.frontend-nav')
<main class="news-account-shell"><div class="news-account-layout">
@include('components.my-account-sidebar')
<section class="news-account-content">
<p class="news-kicker">My Account</p><h1 class="news-title">My news articles</h1><p class="news-subtitle">Track everything you have submitted and its publication status.</p>
<div class="news-actions"><a class="news-button primary" href="{{ route('account.news.create') }}"><i class="fas fa-plus"></i>&nbsp; Submit News</a></div>
@if(session('success'))<div class="news-alert">{{ session('success') }}</div>@endif
<div class="news-list">
@forelse($articles as $article)
<article class="news-row"><div><h2>{{ $article->title }}</h2><div class="news-meta">Submitted {{ $article->created_at->format('d M Y, H:i') }}</div>
@if($article->tags->isNotEmpty())<div class="news-tags">@foreach($article->tags as $tag)<span class="news-tag">{{ $tag->name }}</span>@endforeach</div>@endif</div>
<span class="news-status {{ $article->status === 'published' ? 'published' : 'pending' }}">{{ $article->status === 'published' ? 'Published' : 'Pending review' }}</span></article>
@empty<div class="news-empty"><p>You have not submitted any news articles yet.</p><a class="news-button primary" href="{{ route('account.news.create') }}">Submit your first article</a></div>
@endforelse
</div>
@if($articles->hasPages())<nav class="news-pagination">@if($articles->onFirstPage())<span>Previous</span>@else<a class="news-button" href="{{ $articles->previousPageUrl() }}">Previous</a>@endif @if($articles->hasMorePages())<a class="news-button" href="{{ $articles->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif</nav>@endif
</section></div></main>
@endsection
