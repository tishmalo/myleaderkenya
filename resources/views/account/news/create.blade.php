@extends('layouts.landing')
@section('title', 'Submit News - My Leader Kenya')
@push('styles')@include('account.partials.news-styles')@endpush
@section('content')
<div class="flag-stripe"></div>@include('components.frontend-nav')
<main class="news-account-shell"><div class="news-account-layout">
@include('components.my-account-sidebar')
<section class="news-account-content">
<p class="news-kicker">My Account</p><h1 class="news-title">Submit news</h1><p class="news-subtitle">Your article will remain private until an administrator reviews and publishes it.</p>
<div class="news-actions"><a class="news-button" href="{{ route('account.news.index') }}">&larr; My News</a></div>
@if($errors->any())<div class="news-alert" style="border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.1);color:#fecaca">{{ $errors->first() }}</div>@endif
<form class="news-form" method="POST" action="{{ route('account.news.store') }}" enctype="multipart/form-data">@csrf
<div class="news-form-grid">
<div class="news-field full"><label for="title">Article title *</label><input id="title" name="title" value="{{ old('title') }}" maxlength="255" required>@error('title')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field full">
<x-remote-multiselect name="candidates[]" label="Tagged Aspirants" :search-url="route('account.news.candidates.search')" :selected="$selectedCandidates" placeholder="Search aspirants by name or nickname..." empty-text="No approved aspirants match your search." />
<p class="news-help">Search and tag up to 20 aspirants discussed in this article.</p>@error('candidates')<p class="news-error">{{ $message }}</p>@enderror
</div>
<div class="news-field"><label for="sentiment">Tone of article *</label><select id="sentiment" name="sentiment" required><option value="neutral" {{ old('sentiment', 'neutral') === 'neutral' ? 'selected' : '' }}>Neutral</option><option value="positive" {{ old('sentiment') === 'positive' ? 'selected' : '' }}>Positive</option><option value="negative" {{ old('sentiment') === 'negative' ? 'selected' : '' }}>Negative</option></select><p class="news-help">Choose the overall tone toward the subject.</p>@error('sentiment')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="video_url">Video URL</label><input id="video_url" type="url" name="video_url" value="{{ old('video_url') }}" placeholder="https://...">@error('video_url')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field full"><label for="excerpt">Summary</label><textarea id="excerpt" name="excerpt" rows="3" maxlength="1000">{{ old('excerpt') }}</textarea><p class="news-help">A short introduction shown in news listings.</p>@error('excerpt')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field full"><label for="content">Full article *</label><textarea id="content" name="content" rows="14" required>{{ old('content') }}</textarea>@error('content')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="featured_image">Featured image</label><input id="featured_image" type="file" name="featured_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><p class="news-help">JPG, PNG or WEBP, maximum 5 MB.</p>@error('featured_image')<p class="news-error">{{ $message }}</p>@enderror</div>
@if($tags->isNotEmpty())<div class="news-field full"><label>Tags (up to 10)</label><div class="news-tag-options">@foreach($tags as $tag)<label class="news-check"><input type="checkbox" name="tags[]" value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}><span>{{ $tag->name }}</span></label>@endforeach</div>@error('tags')<p class="news-error">{{ $message }}</p>@enderror</div>@endif
</div>
<button class="news-submit" type="submit">Submit for administrator review</button>
</form>
</section></div></main>
@endsection
