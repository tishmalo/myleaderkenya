@extends('layouts.landing')
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap');

:root {
    --kenya-red:    #BB0000;
    --kenya-black:  #111111;
    --kenya-white:  #F5F5F0;
    --green-bright: #00A86B;
    --kenya-green:  #006600;
}

* { box-sizing: border-box; }
body { font-family: 'Barlow', sans-serif; background: #0a0a0a; color: var(--kenya-white); }
h1,h2,h3,h4 { font-family: 'Oswald', sans-serif; }


.pp-nav {
        background: rgba(10,10,10,0.97);
        border-bottom: 1px solid rgba(255,255,255,0.06);
        backdrop-filter: blur(16px);
        position: sticky; top: 5px; z-index: 100;
    }
    .pp-nav-inner {
        max-width: 1100px; margin: 0 auto; padding: 18px 32px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .pp-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .pp-brand-logo {
        width: 38px; height: 38px; border-radius: 9px;
        background: var(--kenya-red);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Oswald', sans-serif; font-size: 14px; font-weight: 700; color: white;
        position: relative; overflow: hidden;
    }
    .pp-brand-logo::after {
        content: ''; position: absolute; top: 0; right: 0;
        width: 50%; height: 100%; background: var(--kenya-green);
    }
    .pp-brand-logo span { position: relative; z-index: 1; }
    .pp-brand-name {
        font-family: 'Oswald', sans-serif; font-size: 18px; font-weight: 700;
        color: white; letter-spacing: 1px;
    }
    .pp-back {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
        color: rgba(255,255,255,0.35); text-decoration: none;
        transition: color 0.2s;
    }
    .pp-back:hover { color: var(--green-bright); }
/* ── FLAG STRIPE ── */
.flag-stripe {
    height: 4px;
    background: linear-gradient(90deg, var(--kenya-green) 33%, #1a1a1a 33% 66%, var(--kenya-red) 66%);
}

/* ── BACK NAV ── */
.article-back-bar {
    max-width: 900px; margin: 0 auto;
    padding: 28px 32px 0;
}
.article-back-link {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    color: rgba(245,245,240,0.3);
    text-decoration: none; transition: color 0.2s;
}
.article-back-link:hover { color: var(--green-bright); }
.article-back-link i { font-size: 11px; }

/* ── HERO IMAGE AREA ── */
.article-hero {
    position: relative;
    max-width: 1100px; margin: 36px auto 0;
    padding: 0 32px;
}
.article-hero-img-wrap {
    position: relative; border-radius: 20px;
    overflow: hidden; max-height: 520px;
    box-shadow: 0 40px 80px rgba(0,0,0,0.6);
}
.article-hero-img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
    max-height: 520px;
}
.article-hero-img-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(10,10,10,0.85) 0%, rgba(10,10,10,0.1) 50%, transparent 100%);
}

/* Title & meta overlay on hero */
.article-hero-content {
    position: absolute; bottom: 0; left: 0; right: 0;
    padding: 40px 40px 36px;
}
.article-hero-cats {
    display: flex; flex-wrap: wrap; gap: 7px;
    margin-bottom: 16px;
}
.article-hero-cat {
    padding: 4px 12px; border-radius: 6px;
    font-size: 10px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
}
.article-hero-title {
    font-size: clamp(26px, 4vw, 48px);
    font-weight: 700; line-height: 1.05;
    letter-spacing: -0.5px;
    color: white; margin-bottom: 16px;
    text-shadow: 0 2px 20px rgba(0,0,0,0.5);
}
.article-hero-meta {
    display: flex; align-items: center; gap: 16px;
    flex-wrap: wrap;
}
.article-hero-meta-item {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; color: rgba(245,245,240,0.55);
}
.article-hero-meta-item i { font-size: 10px; color: var(--green-bright); }
.article-hero-meta-sep {
    width: 3px; height: 3px; border-radius: 50%;
    background: rgba(245,245,240,0.2);
}

/* No-image fallback header */
.article-no-img-header {
    max-width: 900px; margin: 36px auto 0;
    padding: 0 32px;
}
.article-no-img-cats {
    display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 20px;
}
.article-no-img-cat {
    padding: 4px 12px; border-radius: 6px;
    font-size: 10px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    border: 1px solid;
}
.article-no-img-title {
    font-size: clamp(30px, 4vw, 52px);
    font-weight: 700; line-height: 1.05;
    letter-spacing: -0.5px; color: var(--kenya-white);
    margin-bottom: 20px;
}
.article-no-img-meta {
    display: flex; align-items: center; gap: 16px;
    flex-wrap: wrap; padding-bottom: 24px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.article-no-img-meta-item {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; color: rgba(245,245,240,0.4);
}
.article-no-img-meta-item i { font-size: 10px; color: var(--green-bright); }

/* ── MAIN LAYOUT ── */
.article-layout {
    max-width: 1100px; margin: 0 auto;
    padding: 48px 32px 80px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 300px;
    gap: 40px;
    align-items: start;
}
.article-main {
    display: flex; flex-direction: column; gap: 40px;
    min-width: 0;
}

/* ── SIDEBAR ── */
.article-sidebar {
    position: sticky; top: 88px;
    display: flex; flex-direction: column; gap: 20px;
}
.sb-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 18px; overflow: hidden;
}
.sb-head {
    padding: 16px 20px;
    background: rgba(187,0,0,0.06);
    border-bottom: 1px solid rgba(187,0,0,0.12);
    display: flex; align-items: center; gap: 10px;
}
.sb-head-bar {
    width: 3px; height: 18px; border-radius: 2px;
    background: var(--kenya-red);
}
.sb-head-label {
    font-family: 'Oswald', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    color: rgba(255,120,120,0.85);
}
.sb-body { padding: 14px 16px; }
.sb-tags { display: flex; flex-direction: column; gap: 6px; }
.sb-tag {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px; border-radius: 10px;
    text-decoration: none;
    border: 1px solid transparent;
    transition: background 0.2s, border-color 0.2s;
}
.sb-tag:hover {
    background: rgba(255,255,255,0.04);
    border-color: rgba(255,255,255,0.08);
}
.sb-tag.is-active {
    background: rgba(187,0,0,0.1);
    border-color: rgba(187,0,0,0.28);
}
.sb-tag-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
    background: var(--kenya-red);
}
.sb-tag-name {
    flex: 1; min-width: 0;
    font-size: 13px; font-weight: 500;
    color: rgba(245,245,240,0.72);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.sb-tag.is-active .sb-tag-name { color: white; font-weight: 600; }
.sb-tag-count {
    font-family: 'Oswald', sans-serif;
    font-size: 11px; font-weight: 600;
    color: rgba(245,245,240,0.3);
    background: rgba(255,255,255,0.05);
    padding: 2px 8px; border-radius: 20px;
}
.sb-empty {
    font-size: 12px; color: rgba(245,245,240,0.3);
    padding: 4px 2px;
}
.sb-link-all {
    display: block; text-align: center;
    padding: 12px 16px;
    border-top: 1px solid rgba(255,255,255,0.06);
    font-size: 11px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--green-bright); text-decoration: none;
    transition: background 0.2s;
}
.sb-link-all:hover { background: rgba(0,168,107,0.07); }

/* ── COMMENTS ── */
.comments-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 18px; overflow: hidden;
}
.comments-head {
    padding: 18px 24px;
    background: rgba(0,168,107,0.06);
    border-bottom: 1px solid rgba(0,168,107,0.1);
    display: flex; align-items: center; gap: 10px;
}
.comments-head-bar {
    width: 3px; height: 18px; border-radius: 2px;
    background: var(--green-bright);
}
.comments-head-label {
    font-family: 'Oswald', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    color: rgba(0,168,107,0.85);
}
.comments-head-count {
    font-family: 'Oswald', sans-serif;
    font-size: 11px; color: rgba(245,245,240,0.35);
    margin-left: auto;
}
.comments-list { padding: 8px 24px; }
.comment-item {
    display: flex; gap: 14px;
    padding: 18px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.comment-item:last-child { border-bottom: none; }
.comment-avatar {
    width: 38px; height: 38px; border-radius: 10px;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Oswald', sans-serif;
    font-size: 15px; font-weight: 700;
    color: white;
    background: linear-gradient(135deg, var(--kenya-red), var(--kenya-green));
}
.comment-main { min-width: 0; flex: 1; }
.comment-meta {
    display: flex; align-items: baseline; gap: 10px;
    flex-wrap: wrap; margin-bottom: 6px;
}
.comment-author {
    font-family: 'Oswald', sans-serif;
    font-size: 14px; font-weight: 600;
    color: var(--kenya-white);
}
.comment-date { font-size: 11px; color: rgba(245,245,240,0.3); }
.comment-body {
    font-size: 14px; line-height: 1.75;
    color: rgba(245,245,240,0.65);
    white-space: pre-line;
    word-break: break-word;
}
.comments-empty {
    padding: 28px 24px;
    text-align: center;
    font-size: 13px; color: rgba(245,245,240,0.3);
}
.comment-form-wrap {
    padding: 20px 24px 24px;
    border-top: 1px solid rgba(255,255,255,0.06);
    background: rgba(255,255,255,0.015);
}
.comment-form-label {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    color: rgba(245,245,240,0.4);
    margin-bottom: 10px;
}
.comment-form textarea {
    width: 100%;
    min-height: 100px;
    padding: 13px 15px;
    background: #0f0f0f;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    color: var(--kenya-white);
    font-family: 'Barlow', sans-serif;
    font-size: 14px; line-height: 1.6;
    resize: vertical;
    transition: border-color 0.2s;
}
.comment-form textarea:focus {
    outline: none;
    border-color: rgba(0,168,107,0.4);
}
.comment-form textarea::placeholder { color: rgba(245,245,240,0.22); }
.comment-form-foot {
    display: flex; align-items: center; justify-content: space-between;
    gap: 14px; margin-top: 12px; flex-wrap: wrap;
}
.comment-form-hint { font-size: 11px; color: rgba(245,245,240,0.28); }
.comment-form-submit {
    padding: 10px 22px; border: none; border-radius: 10px;
    background: var(--green-bright); color: #05231a;
    font-family: 'Oswald', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    cursor: pointer;
    transition: filter 0.2s;
}
.comment-form-submit:hover { filter: brightness(1.1); }
.comment-alert {
    padding: 11px 14px; border-radius: 10px;
    font-size: 13px; line-height: 1.5;
    margin-bottom: 14px;
}
.comment-alert-success {
    background: rgba(0,168,107,0.1);
    border: 1px solid rgba(0,168,107,0.25);
    color: rgba(0,200,130,0.9);
}
.comment-alert-error {
    background: rgba(187,0,0,0.1);
    border: 1px solid rgba(187,0,0,0.3);
    color: rgba(255,120,120,0.9);
}
.comment-alert-info {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    color: rgba(245,245,240,0.55);
}
.comment-login-link { color: var(--green-bright); font-weight: 600; text-decoration: underline; }
.comment-error-text { color: rgba(255,120,120,0.85); font-size: 11px; margin-top: 6px; }

/* Meta bar below image (when image exists) */
.article-meta-bar {
    display: flex; align-items: center; gap: 20px;
    padding: 18px 24px;
    background: #161616;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 12px;
    flex-wrap: wrap;
}
.article-meta-bar-item {
    display: flex; align-items: center; gap: 7px;
    font-size: 13px; color: rgba(245,245,240,0.4);
}
.article-meta-bar-item i { font-size: 10px; color: var(--green-bright); }
.article-meta-bar-sep {
    flex: 1; height: 1px;
    background: rgba(255,255,255,0.05);
}

/* ── ASPIRANTS CARD ── */
.article-aspirants-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 18px; overflow: hidden;
}
.article-aspirants-head {
    padding: 16px 22px;
    background: rgba(0,168,107,0.06);
    border-bottom: 1px solid rgba(0,168,107,0.1);
    display: flex; align-items: center; gap: 10px;
}
.article-aspirants-head-bar {
    width: 3px; height: 18px; border-radius: 2px;
    background: var(--green-bright);
}
.article-aspirants-head-label {
    font-family: 'Oswald', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    color: rgba(0,168,107,0.8);
}
.article-aspirants-list {
    padding: 16px 18px;
    display: flex; flex-wrap: wrap; gap: 12px;
}
.article-aspirant-chip {
    display: flex; align-items: center; gap: 12px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 12px; padding: 10px 14px;
    text-decoration: none;
    transition: border-color 0.2s, background 0.2s;
    min-width: 200px; flex: 1;
}
.article-aspirant-chip:hover {
    border-color: rgba(0,168,107,0.3);
    background: rgba(0,168,107,0.06);
}
.article-aspirant-avatar {
    width: 44px; height: 44px; border-radius: 10px;
    object-fit: cover; flex-shrink: 0;
    border: 1px solid rgba(255,255,255,0.08);
}
.article-aspirant-avatar-placeholder {
    width: 44px; height: 44px; border-radius: 10px;
    background: linear-gradient(135deg, rgba(187,0,0,0.15), rgba(0,102,0,0.15));
    display: flex; align-items: center; justify-content: center;
    font-family: 'Oswald', sans-serif;
    font-size: 16px; font-weight: 700;
    color: rgba(255,255,255,0.2);
    flex-shrink: 0;
    border: 1px solid rgba(255,255,255,0.06);
}
.article-aspirant-info {}
.article-aspirant-name {
    font-family: 'Oswald', sans-serif;
    font-size: 15px; font-weight: 600;
    color: var(--kenya-white); line-height: 1.1;
    margin-bottom: 2px;
    transition: color 0.2s;
}
.article-aspirant-chip:hover .article-aspirant-name { color: var(--green-bright); }
.article-aspirant-role {
    font-size: 11px; color: rgba(245,245,240,0.3);
}

/* ── ARTICLE BODY ── */
.article-body-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 18px;
    padding: 40px 44px;
    position: relative; overflow: hidden;
}
.article-body-card::before {
    content: '';
    position: absolute; top: 0; left: 0; width: 3px; bottom: 0;
    background: linear-gradient(180deg, var(--kenya-red) 0%, var(--kenya-green) 100%);
}
.article-body {
    font-size: 17px; line-height: 1.9;
    color: rgba(245,245,240,0.7);
}
.article-body p { margin-bottom: 20px; }
.article-body p:last-child { margin-bottom: 0; }
.article-body strong { color: var(--kenya-white); font-weight: 600; }
.article-body em { color: rgba(245,245,240,0.6); font-style: italic; }
.article-body h2 {
    font-size: 26px; font-weight: 700;
    color: var(--kenya-white); margin: 32px 0 14px;
}
.article-body h3 {
    font-size: 20px; font-weight: 600;
    color: var(--kenya-white); margin: 24px 0 10px;
}
.article-body a {
    color: var(--green-bright); text-decoration: underline;
    text-underline-offset: 3px;
}
.article-body blockquote {
    border-left: 3px solid var(--kenya-red);
    margin: 24px 0; padding: 16px 24px;
    background: rgba(187,0,0,0.06); border-radius: 0 10px 10px 0;
    font-style: italic; color: rgba(245,245,240,0.55);
}

/* ── VIDEO ── */
.article-video-wrap {
    border-radius: 16px; overflow: hidden;
    aspect-ratio: 16/9;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    border: 1px solid rgba(255,255,255,0.07);
}
.article-video-wrap iframe {
    width: 100%; height: 100%; display: block; border: none;
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .article-back-bar { padding: 20px 16px 0; }
    .article-hero { padding: 0 16px; margin-top: 24px; }
    .article-hero-content { padding: 24px 20px 20px; }
    .article-hero-title { font-size: 22px; }
    .article-no-img-header { padding: 0 16px; margin-top: 24px; }
    .article-layout {
        padding: 28px 16px 60px;
        grid-template-columns: minmax(0, 1fr);
        gap: 28px;
    }
    .article-main { gap: 24px; }
    .article-sidebar { position: static; }
    .article-body-card { padding: 24px 22px; }
    .article-body { font-size: 15px; }
    .article-aspirant-chip { min-width: 100%; }
    .article-meta-bar { gap: 12px; }
    .comments-list, .comment-form-wrap { padding-left: 18px; padding-right: 18px; }
}
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')
<!-- BACK NAV -->
<div class="article-back-bar">
    <a href="{{ route('news.public') }}" class="article-back-link">
        <i class="fas fa-arrow-left"></i> All News
    </a>
    
</div>

{{-- ── HERO: with image ── --}}
@if($article->featured_image)
<div class="article-hero">
    <div class="article-hero-img-wrap">
        <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->title }}">
        <div class="article-hero-img-overlay"></div>
        <div class="article-hero-content">
            @if($article->tags->count())
            <div class="article-hero-cats">
                @foreach($article->tags as $cat)
                <span class="article-hero-cat"
                      style="background: {{ $cat->color ?? '#BB0000' }}28; color: {{ $cat->color ?? '#ff6666' }}; border-color: {{ $cat->color ?? '#BB0000' }}40">
                    {{ $cat->name }}
                </span>
                @endforeach
            </div>
            @endif
            <h1 class="article-hero-title">{{ $article->title }}</h1>
            <div class="article-hero-meta">
                <span class="article-hero-meta-item">
                    <i class="fas fa-user"></i> {{ $article->author->name ?? 'Admin' }}
                </span>
                <span class="article-hero-meta-sep"></span>
                <span class="article-hero-meta-item">
                    <i class="fas fa-calendar-alt"></i> {{ $article->created_at->format('d F Y') }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ── HERO: no image ── --}}
@else
<div class="article-no-img-header">
    @if($article->tags->count())
    <div class="article-no-img-cats">
        @foreach($article->tags as $cat)
        <span class="article-no-img-cat"
              style="background: {{ $cat->color ?? '#BB0000' }}18; color: {{ $cat->color ?? '#ff6666' }}; border-color: {{ $cat->color ?? '#BB0000' }}35">
            {{ $cat->name }}
        </span>
        @endforeach
    </div>
    @endif
    <h1 class="article-no-img-title">{{ $article->title }}</h1>
    <div class="article-no-img-meta">
        <span class="article-no-img-meta-item">
            <i class="fas fa-user"></i> {{ $article->author->name ?? 'Admin' }}
        </span>
        <span class="article-no-img-meta-item">
            <i class="fas fa-calendar-alt"></i> {{ $article->created_at->format('d F Y') }}
        </span>
    </div>
</div>
@endif

<!-- MAIN LAYOUT -->
<div class="article-layout">
  <div class="article-main">

    {{-- Meta bar (only shown when image exists, since meta is in overlay otherwise) --}}
    @if($article->featured_image)
    <div class="article-meta-bar">
        <span class="article-meta-bar-item">
            <i class="fas fa-user"></i> {{ $article->author->name ?? 'Admin' }}
        </span>
        <div class="article-meta-bar-sep"></div>
        <span class="article-meta-bar-item">
            <i class="fas fa-calendar-alt"></i> {{ $article->created_at->format('d F Y') }}
        </span>
        <div class="article-meta-bar-sep"></div>
        <span class="article-meta-bar-item">
            <i class="fas fa-clock"></i>
            {{ max(1, (int) ceil(str_word_count(strip_tags($article->content)) / 200)) }} min read
        </span>
    </div>
    @endif

    <!-- Featured Aspirants -->
    @if($article->candidates->count() > 0)
    <div class="article-aspirants-card">
        <div class="article-aspirants-head">
            <div class="article-aspirants-head-bar"></div>
            <span class="article-aspirants-head-label">This story features</span>
        </div>
        <div class="article-aspirants-list">
            @foreach($article->candidates as $candidate)
            <a href="{{ route('aspirants.show', $candidate) }}" class="article-aspirant-chip">
                @if($candidate->profile_picture)
                    <img src="{{ Storage::url($candidate->profile_picture) }}"
                         class="article-aspirant-avatar" alt="{{ $candidate->name }}">
                @else
                    <div class="article-aspirant-avatar-placeholder">
                        {{ strtoupper(substr($candidate->name, 0, 1)) }}
                    </div>
                @endif
                <div class="article-aspirant-info">
                    <div class="article-aspirant-name">{{ $candidate->name }}</div>
                    <div class="article-aspirant-role">{{ $candidate->position->name ?? 'Aspirant' }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Article Body -->
    <div class="article-body-card">
        <div class="article-body">
            {!! nl2br(e($article->content)) !!}
        </div>
    </div>

    <!-- Video -->
    @if($article->video_url)
    <div class="article-video-wrap">
        <iframe src="{{ $article->video_url }}" allowfullscreen loading="lazy"></iframe>
    </div>
    @endif

    <!-- Comments -->
    <div class="comments-card" id="comments">
        <div class="comments-head">
            <div class="comments-head-bar"></div>
            <span class="comments-head-label">Discussion</span>
            <span class="comments-head-count">
                {{ $commentCount }} {{ Str::plural('comment', $commentCount) }}
            </span>
        </div>

        @if(session('comment_notice'))
            <div class="comment-form-wrap" style="border-top: none; padding-bottom: 0; background: none;">
                <div class="comment-alert comment-alert-success">{{ session('comment_notice') }}</div>
            </div>
        @endif

        @if($comments->isEmpty())
            <div class="comments-empty">No comments yet. Be the first to share your thoughts.</div>
        @else
            <div class="comments-list">
                @foreach($comments as $comment)
                <div class="comment-item">
                    <div class="comment-avatar">
                        {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="comment-main">
                        <div class="comment-meta">
                            <span class="comment-author">{{ $comment->user->name ?? 'Deleted user' }}</span>
                            <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="comment-body">{{ $comment->body }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        <div class="comment-form-wrap">
            @auth
                <form method="POST" action="{{ route('news.comments.store', $article->slug) }}" class="comment-form">
                    @csrf
                    <label for="comment-body" class="comment-form-label">Join the discussion</label>
                    <textarea id="comment-body" name="body" maxlength="2000" required
                              placeholder="Share your thoughts on this story…">{{ old('body') }}</textarea>
                    @error('body')
                        <div class="comment-error-text">{{ $message }}</div>
                    @enderror
                    <div class="comment-form-foot">
                        <span class="comment-form-hint">Comments are reviewed by an administrator before they appear.</span>
                        <button type="submit" class="comment-form-submit">Post Comment</button>
                    </div>
                </form>
            @else
                <div class="comment-alert comment-alert-info">
                    Please <a href="{{ route('login') }}" class="comment-login-link">log in</a>
                    to leave a comment on this story.
                </div>
            @endauth
        </div>
    </div>

  </div>

  <!-- Sidebar -->
  <aside class="article-sidebar">
      <div class="sb-card">
          <div class="sb-head">
              <div class="sb-head-bar"></div>
              <span class="sb-head-label">Categories</span>
          </div>
          <div class="sb-body">
              @if(($sidebarTags ?? collect())->isEmpty())
                  <div class="sb-empty">No categories yet.</div>
              @else
                  <div class="sb-tags">
                      @foreach($sidebarTags as $sidebarTag)
                      @php $isActive = $article->tags->contains('id', $sidebarTag->id); @endphp
                      <a href="{{ route('news.public', ['tag' => $sidebarTag->slug]) }}"
                         class="sb-tag {{ $isActive ? 'is-active' : '' }}">
                          <span class="sb-tag-dot" style="background: {{ $sidebarTag->color ?? '#BB0000' }}"></span>
                          <span class="sb-tag-name">{{ $sidebarTag->name }}</span>
                          <span class="sb-tag-count">{{ $sidebarTag->articles_count }}</span>
                      </a>
                      @endforeach
                  </div>
              @endif
          </div>
          <a href="{{ route('news.public') }}" class="sb-link-all">All News</a>
      </div>
  </aside>

</div>

@endsection
