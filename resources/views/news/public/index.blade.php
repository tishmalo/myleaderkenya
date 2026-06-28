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

/* ── FLAG STRIPE ── */
.flag-stripe {
    height: 4px;
    background: linear-gradient(90deg, var(--kenya-green) 33%, #1a1a1a 33% 66%, var(--kenya-red) 66%);
}

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
/* ── HERO ── */
.news-hero {
    position: relative;
    padding: 72px 32px 56px;
    text-align: center;
    overflow: hidden;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.news-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 60% 100% at 20% 60%, rgba(187,0,0,0.13) 0%, transparent 60%),
        radial-gradient(ellipse 60% 100% at 80% 40%, rgba(0,102,0,0.13) 0%, transparent 60%);
    pointer-events: none;
}
.news-hero-pattern {
    position: absolute; inset: 0; pointer-events: none;
    background-image: repeating-linear-gradient(
        -45deg, transparent, transparent 40px,
        rgba(255,255,255,0.012) 40px, rgba(255,255,255,0.012) 41px
    );
}
.news-hero-eyebrow {
    display: inline-flex; align-items: center; gap: 10px;
    border: 1px solid rgba(187,0,0,0.4);
    background: rgba(187,0,0,0.08);
    padding: 6px 18px; border-radius: 40px;
    font-size: 11px; font-weight: 700; letter-spacing: 3px;
    color: #ff6666; text-transform: uppercase; margin-bottom: 24px;
    position: relative;
}
.news-hero-eyebrow .dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--kenya-red);
    animation: pulse 1.6s infinite;
}
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(1.6)} }

.news-hero h1 {
    font-size: clamp(44px, 6vw, 76px);
    font-weight: 700; line-height: 0.95;
    letter-spacing: -1px; margin-bottom: 16px;
    position: relative;
}
.news-hero h1 em {
    font-style: normal;
    background: linear-gradient(135deg, var(--kenya-red), #ff4444);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.news-hero p {
    font-size: 17px; color: rgba(245,245,240,0.4);
    max-width: 440px; margin: 0 auto;
    position: relative;
}

/* ── CATEGORY FILTER BAR ── */
.news-filter-wrap {
    max-width: 1280px; margin: 0 auto;
    padding: 0 32px;
    margin-top: -24px; margin-bottom: 48px;
    position: relative; z-index: 10;
}
.news-filter-bar {
    background: #161616;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 14px 18px;
    display: flex; align-items: center; gap: 8px;
    overflow-x: auto; scrollbar-width: none;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.news-filter-bar::-webkit-scrollbar { display: none; }

.news-filter-label {
    font-family: 'Oswald', sans-serif;
    font-size: 10px; font-weight: 700;
    letter-spacing: 3px; text-transform: uppercase;
    color: rgba(245,245,240,0.22);
    white-space: nowrap; padding-right: 12px;
    border-right: 1px solid rgba(255,255,255,0.07);
    margin-right: 4px; flex-shrink: 0;
}
.news-cat-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    white-space: nowrap; text-decoration: none;
    transition: all 0.2s; flex-shrink: 0;
    border: 1px solid transparent;
    color: rgba(245,245,240,0.45);
    background: transparent;
}
.news-cat-btn:hover {
    background: rgba(255,255,255,0.05);
    color: var(--kenya-white);
    border-color: rgba(255,255,255,0.08);
}
.news-cat-btn.active {
    background: var(--kenya-red);
    color: white;
    border-color: var(--kenya-red);
    box-shadow: 0 0 20px rgba(187,0,0,0.3);
}
.news-cat-dot {
    width: 8px; height: 8px; border-radius: 50%;
    flex-shrink: 0;
}

/* ── RESULTS META ── */
.news-results-meta {
    max-width: 1280px; margin: 0 auto 28px;
    padding: 0 32px;
    display: flex; align-items: center; gap: 16px;
}
.news-results-count {
    font-size: 13px; color: rgba(245,245,240,0.3);
    white-space: nowrap;
}
.news-results-count strong { color: var(--kenya-red); font-weight: 700; }
.news-results-line {
    flex: 1; height: 1px;
    background: linear-gradient(90deg, rgba(187,0,0,0.2), transparent);
}

/* ── GRID ── */
.news-grid {
    max-width: 1280px; margin: 0 auto;
    padding: 0 32px 80px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
}

/* ── NEWS CARD ── */
.news-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 20px; overflow: hidden;
    display: flex; flex-direction: column;
    transition: border-color 0.3s, transform 0.3s, box-shadow 0.3s;
    position: relative;
}
.news-card:hover {
    border-color: rgba(187,0,0,0.35);
    transform: translateY(-4px);
    box-shadow: 0 24px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(187,0,0,0.1);
}

/* Image */
.news-card-img {
    position: relative; height: 210px; overflow: hidden;
    flex-shrink: 0;
}
.news-card-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.5s ease;
}
.news-card:hover .news-card-img img { transform: scale(1.06); }
.news-card-img-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, #141414 0%, rgba(20,20,20,0.1) 60%, transparent 100%);
}
.news-card-no-img {
    height: 210px; flex-shrink: 0;
    background: linear-gradient(135deg, rgba(187,0,0,0.1) 0%, rgba(0,102,0,0.1) 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 48px; opacity: 0.3;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

/* Category tags on image */
.news-card-cats {
    position: absolute; top: 12px; left: 12px;
    display: flex; flex-wrap: wrap; gap: 6px;
}
.news-card-cat {
    padding: 4px 10px; border-radius: 6px;
    font-size: 10px; font-weight: 700;
    letter-spacing: 1px; text-transform: uppercase;
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
}

/* Body */
.news-card-body {
    padding: 20px 22px; flex: 1;
    display: flex; flex-direction: column;
}
.news-card-title {
    font-family: 'Oswald', sans-serif;
    font-size: 19px; font-weight: 600;
    line-height: 1.2; color: var(--kenya-white);
    margin-bottom: 10px;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
    transition: color 0.2s; text-decoration: none;
}
.news-card:hover .news-card-title { color: #ff6666; }
.news-card-excerpt {
    font-size: 14px; line-height: 1.65;
    color: rgba(245,245,240,0.4);
    display: -webkit-box; -webkit-line-clamp: 3;
    -webkit-box-orient: vertical; overflow: hidden;
    margin-bottom: 18px;
}

/* Aspirants strip */
.news-card-aspirants {
    margin-bottom: 16px;
}
.news-card-aspirants-label {
    font-size: 10px; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    color: var(--green-bright); margin-bottom: 8px;
    display: flex; align-items: center; gap: 6px;
}
.news-card-aspirants-label::before {
    content: ''; display: block;
    width: 12px; height: 1px; background: var(--green-bright);
}
.news-card-aspirant-tags {
    display: flex; flex-wrap: wrap; gap: 6px;
}
.news-card-aspirant-tag {
    display: inline-block;
    padding: 4px 10px; border-radius: 6px;
    font-size: 11px; font-weight: 600;
    background: rgba(0,168,107,0.08);
    border: 1px solid rgba(0,168,107,0.18);
    color: rgba(0,168,107,0.8);
    text-decoration: none; transition: all 0.2s;
}
.news-card-aspirant-tag:hover {
    background: rgba(0,168,107,0.16);
    color: var(--green-bright);
}

/* Footer */
.news-card-footer {
    margin-top: auto;
    padding-top: 14px;
    border-top: 1px solid rgba(255,255,255,0.05);
    display: flex; align-items: center; justify-content: space-between;
}
.news-card-author {
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; color: rgba(245,245,240,0.35);
}
.news-card-author-icon {
    width: 24px; height: 24px; border-radius: 50%;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.08);
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; color: rgba(245,245,240,0.3);
}
.news-card-date {
    font-size: 11px; color: rgba(245,245,240,0.25);
    display: flex; align-items: center; gap: 5px;
}
.news-card-date i { font-size: 9px; }

/* Read more arrow */
.news-card-read {
    position: absolute; bottom: 20px; right: 20px;
    width: 30px; height: 30px;
    background: var(--kenya-red);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: white;
    opacity: 0; transform: translateX(-4px);
    transition: opacity 0.25s, transform 0.25s, background 0.2s;
    text-decoration: none;
}
.news-card:hover .news-card-read {
    opacity: 1; transform: translateX(0);
}
.news-card:hover .news-card-read:hover { background: var(--green-bright); }

/* ── EMPTY STATE ── */
.news-empty {
    grid-column: 1 / -1;
    text-align: center; padding: 80px 20px;
}
.news-empty-icon { font-size: 52px; opacity: 0.2; margin-bottom: 20px; }
.news-empty h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 26px; color: rgba(245,245,240,0.3); margin-bottom: 8px;
}
.news-empty p { font-size: 14px; color: rgba(245,245,240,0.18); }

/* ── PAGINATION ── */
.news-pagination {
    max-width: 1280px; margin: 0 auto;
    padding: 0 32px 80px;
    display: flex; justify-content: center;
}
.news-pagination nav { display: flex; gap: 6px; }
.news-pagination .page-link {
    display: flex; align-items: center; justify-content: center;
    min-width: 40px; height: 40px; padding: 0 12px;
    background: #161616;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 10px;
    color: rgba(245,245,240,0.4);
    font-size: 14px; text-decoration: none;
    transition: all 0.2s;
}
.news-pagination .page-link:hover { border-color: var(--kenya-red); color: #ff6666; }
.news-pagination .page-item.active .page-link {
    background: var(--kenya-red); border-color: var(--kenya-red); color: white;
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .news-hero { padding: 56px 16px 44px; }
    .news-filter-wrap { padding: 0 16px; }
    .news-results-meta { padding: 0 16px; }
    .news-grid { grid-template-columns: 1fr; padding: 0 16px 60px; }
    .news-filter-label { display: none; }
}
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')
<!-- HERO -->
<div class="news-hero">
    <div class="news-hero-pattern"></div>
    <div class="news-hero-eyebrow"><span class="dot"></span> Kenya 2027</div>
    <h1>Blogs &amp; <em>Updates</em></h1>
    <p>Stories, analysis, and voices shaping Kenya's future.</p>
</div>

<!-- CATEGORY FILTER -->
<div class="news-filter-wrap">
    <div class="news-filter-bar">
        <span class="news-filter-label">Topics</span>

        <a href="{{ route('news.public') }}"
           class="news-cat-btn {{ !request('category') ? 'active' : '' }}">
            <span class="news-cat-dot" style="background: rgba(245,245,240,0.3)"></span>
            All Blogs
        </a>

        @foreach($categories as $category)
        <a href="{{ route('news.public', ['category' => $category->slug]) }}"
           class="news-cat-btn {{ request('category') == $category->slug ? 'active' : '' }}">
            <span class="news-cat-dot" style="background: {{ $category->color ?? '#BB0000' }}"></span>
            {{ $category->name }}
        </a>
        @endforeach
    </div>
</div>

<!-- RESULTS META -->
<div class="news-results-meta">
    <span class="news-results-count">
        <strong>{{ $articles->total() }}</strong> article{{ $articles->total() != 1 ? 's' : '' }}
        @if(request('category')) &nbsp;in this category @endif
    </span>
    <div class="news-results-line"></div>
</div>

<!-- GRID -->
<div class="news-grid">
    @forelse($articles as $article)
    <div class="news-card">

        <!-- Image -->
        @if($article->featured_image)
        <div class="news-card-img">
            <img src="{{ Storage::url($article->featured_image) }}"
                 alt="{{ $article->title }}" loading="lazy">
            <div class="news-card-img-overlay"></div>
            @if($article->categories->count())
            <div class="news-card-cats">
                @foreach($article->categories->take(2) as $cat)
                <span class="news-card-cat"
                      style="background: {{ $cat->color ?? '#BB0000' }}22; color: {{ $cat->color ?? '#ff6666' }}; border-color: {{ $cat->color ?? '#BB0000' }}33">
                    {{ $cat->name }}
                </span>
                @endforeach
            </div>
            @endif
        </div>
        @else
        <div class="news-card-no-img">📰</div>
        @if($article->categories->count())
        <div style="padding: 12px 16px 0; display:flex; gap:6px; flex-wrap:wrap;">
            @foreach($article->categories->take(2) as $cat)
            <span class="news-card-cat"
                  style="background: {{ $cat->color ?? '#BB0000' }}18; color: {{ $cat->color ?? '#ff6666' }}; border-color: {{ $cat->color ?? '#BB0000' }}30; position:static; backdrop-filter:none;">
                {{ $cat->name }}
            </span>
            @endforeach
        </div>
        @endif
        @endif

        <!-- Body -->
        <div class="news-card-body">
            <a href="{{ route('news.public.show', $article->slug) }}" class="news-card-title">
                {{ $article->title }}
            </a>

            @if($article->excerpt)
            <p class="news-card-excerpt">{{ $article->excerpt }}</p>
            @endif

            @if($article->candidates->count() > 0)
            <div class="news-card-aspirants">
                <div class="news-card-aspirants-label">Featured Aspirants</div>
                <div class="news-card-aspirant-tags">
                    @foreach($article->candidates->take(3) as $candidate)
                    <a href="{{ route('aspirants.show', $candidate) }}"
                       class="news-card-aspirant-tag">
                        {{ $candidate->name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="news-card-footer">
                <div class="news-card-author">
                    <div class="news-card-author-icon"><i class="fas fa-user"></i></div>
                    {{ $article->author->name ?? 'Admin' }}
                </div>
                <div class="news-card-date">
                    <i class="fas fa-calendar-alt"></i>
                    {{ $article->created_at->format('d M Y') }}
                </div>
            </div>
        </div>

        <!-- Hover read arrow -->
        <a href="{{ route('news.public.show', $article->slug) }}" class="news-card-read">
            <i class="fas fa-arrow-right"></i>
        </a>

    </div>
    @empty
    <div class="news-empty">
        <div class="news-empty-icon">📰</div>
        <h3>No articles yet</h3>
        <p>Check back soon for the latest news and updates.</p>
    </div>
    @endforelse
</div>

<!-- PAGINATION -->
@if($articles->hasPages())
<div class="news-pagination">
    {{ $articles->links() }}
</div>
@endif

@endsection