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

/* ── TOP STRIPE ── */
.flag-stripe {
    height: 4px;
    background: linear-gradient(90deg, var(--kenya-green) 33%, #1a1a1a 33% 66%, var(--kenya-red) 66%);
}

/* ── BACK NAV ── */
.asp-back-bar {
    max-width: 1280px; margin: 0 auto;
    padding: 28px 32px 0;
}
.asp-back-link {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    color: rgba(245,245,240,0.35);
    text-decoration: none;
    transition: color 0.2s;
}
.asp-back-link:hover { color: var(--green-bright); }
.asp-back-link i { font-size: 11px; }

/* ── HERO BANNER ── */
.asp-profile-hero {
    position: relative;
    height: 260px; overflow: hidden;
    margin-bottom: 0;
}
.asp-profile-hero-bg {
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 60% 100% at 30% 50%, rgba(187,0,0,0.2) 0%, transparent 60%),
        radial-gradient(ellipse 60% 100% at 70% 50%, rgba(0,102,0,0.2) 0%, transparent 60%),
        #0d0d0d;
}
.asp-profile-hero-pattern {
    position: absolute; inset: 0;
    background-image: repeating-linear-gradient(
        -45deg, transparent, transparent 40px,
        rgba(255,255,255,0.012) 40px, rgba(255,255,255,0.012) 41px
    );
}

/* ── MAIN LAYOUT ── */
.asp-profile-wrap {
    max-width: 1280px; margin: 0 auto;
    padding: 0 32px 80px;
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 32px;
    margin-top: -120px;
    position: relative; z-index: 10;
}

/* ── SIDEBAR ── */
.asp-sidebar { display: flex; flex-direction: column; gap: 20px; }

/* Photo card */
.asp-photo-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(0,0,0,0.6);
}
.asp-photo-wrap {
    position: relative;
    aspect-ratio: 4/5;
    overflow: hidden;
}
.asp-photo-wrap img {
    width: 100%; height: 100%;
    object-fit: cover; object-position: top center;
}
.asp-photo-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, rgba(187,0,0,0.15) 0%, rgba(0,102,0,0.15) 100%);
    display: flex; align-items: center; justify-content: center;
}
.asp-photo-placeholder .initials {
    font-family: 'Oswald', sans-serif;
    font-size: 80px; font-weight: 700;
    color: rgba(255,255,255,0.1);
}
.asp-photo-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, #141414 0%, rgba(20,20,20,0.1) 50%, transparent 100%);
}
.asp-photo-position {
    position: absolute; top: 14px; left: 14px;
    background: rgba(0,0,0,0.7); backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; padding: 5px 12px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 1px; text-transform: uppercase;
    color: rgba(245,245,240,0.75);
}
.asp-photo-body {
    padding: 20px 22px 24px;
}
.asp-candidate-name {
    font-size: 26px; font-weight: 700;
    line-height: 1.05; color: var(--kenya-white);
    margin-bottom: 4px;
}
.asp-candidate-nick {
    font-size: 14px; color: rgba(0,168,107,0.8);
    font-style: italic; margin-bottom: 16px;
}
.asp-photo-divider {
    height: 1px;
    background: linear-gradient(90deg, rgba(0,168,107,0.3), rgba(187,0,0,0.2), transparent);
    margin-bottom: 16px;
}

/* Location pills */
.asp-location-pills {
    display: flex; flex-wrap: wrap; gap: 8px;
}
.asp-pill {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 6px; padding: 5px 10px;
    font-size: 12px; color: rgba(245,245,240,0.55);
}
.asp-pill i { font-size: 9px; color: var(--green-bright); }

/* Info card (bloc etc) */
.asp-info-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px;
    overflow: hidden;
}
.asp-info-card-header {
    padding: 14px 18px;
    background: rgba(255,255,255,0.03);
    border-bottom: 1px solid rgba(255,255,255,0.06);
    font-family: 'Oswald', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 2.5px; text-transform: uppercase;
    color: rgba(245,245,240,0.3);
}
.asp-info-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 13px 18px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 13px;
}
.asp-info-row:last-child { border-bottom: none; }
.asp-info-label { color: rgba(245,245,240,0.35); }
.asp-info-value { color: var(--kenya-white); font-weight: 600; text-align: right; max-width: 60%; }
.asp-info-value.green { color: var(--green-bright); }

/* ── MAIN CONTENT ── */
.asp-main { display: flex; flex-direction: column; gap: 24px; }

/* Section card */
.asp-section-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 20px;
    overflow: hidden;
}
.asp-section-head {
    padding: 24px 28px 0;
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 20px;
}
.asp-section-head-bar {
    width: 3px; height: 24px; border-radius: 2px;
    background: linear-gradient(180deg, var(--kenya-red), var(--kenya-green));
    flex-shrink: 0;
}
.asp-section-title {
    font-size: 20px; font-weight: 700;
    letter-spacing: 0.5px; color: var(--kenya-white);
}
.asp-section-body { padding: 0 28px 28px; }

/* About text */
.asp-about-text {
    font-size: 16px; line-height: 1.85;
    color: rgba(245,245,240,0.6);
}
.asp-about-text p { margin-bottom: 16px; }
.asp-about-text p:last-child { margin-bottom: 0; }

/* Contact grid */
.asp-contact-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.asp-contact-item {
    display: flex; align-items: center; gap: 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px; padding: 14px 16px;
    transition: border-color 0.2s;
}
.asp-contact-item:hover { border-color: rgba(0,168,107,0.25); }
.asp-contact-icon {
    width: 40px; height: 40px; flex-shrink: 0;
    border-radius: 10px;
    background: rgba(0,168,107,0.1);
    border: 1px solid rgba(0,168,107,0.15);
    display: flex; align-items: center; justify-content: center;
    color: var(--green-bright); font-size: 14px;
}
.asp-contact-label { font-size: 11px; color: rgba(245,245,240,0.3); letter-spacing: 0.5px; margin-bottom: 2px; }
.asp-contact-value { font-size: 14px; font-weight: 600; color: var(--kenya-white); }

/* News articles */
.asp-news-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.asp-news-card {
    background: #0e0e0e;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 14px; overflow: hidden;
    text-decoration: none;
    transition: border-color 0.25s, transform 0.25s;
    display: flex; flex-direction: column;
}
.asp-news-card:hover {
    border-color: rgba(0,168,107,0.3);
    transform: translateY(-2px);
}
.asp-news-card-img {
    height: 150px; overflow: hidden;
}
.asp-news-card-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.4s;
}
.asp-news-card:hover .asp-news-card-img img { transform: scale(1.05); }
.asp-news-card-body { padding: 14px 16px 16px; flex: 1; display: flex; flex-direction: column; }
.asp-news-card-title {
    font-family: 'Oswald', sans-serif;
    font-size: 15px; font-weight: 600;
    color: var(--kenya-white); line-height: 1.3;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
    margin-bottom: 10px;
}
.asp-news-card-date {
    margin-top: auto;
    font-size: 11px; color: rgba(245,245,240,0.25);
    letter-spacing: 0.5px;
}

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    .asp-profile-wrap {
        grid-template-columns: 1fr;
        margin-top: -60px;
        padding: 0 16px 60px;
    }
    .asp-photo-wrap { aspect-ratio: 3/2; }
    .asp-contact-grid { grid-template-columns: 1fr; }
    .asp-news-grid { grid-template-columns: 1fr; }
    .asp-back-bar { padding: 20px 16px 0; }
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
</style>

<div class="flag-stripe"></div>

<!-- BACK NAV -->
<!-- <div class="asp-back-bar"> -->
    
    <!-- <a href="{{ route('aspirants.public') }}" class="asp-back-link">
        <i class="fas fa-arrow-left"></i> All Aspirants
    </a> -->
<!-- </div> -->
<nav class="pp-nav">
        <div class="pp-nav-inner">
            <a href="/" class="pp-brand">
                <div class="pp-brand-logo"><span>TK</span></div>
                <div class="pp-brand-name">TUKO KADI</div>
            </a>
            <a href="{{ url('/') }}" class="pp-back">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </nav>
<!-- HERO BANNER -->
<div class="asp-profile-hero">
    <div class="asp-profile-hero-bg"></div>
    <div class="asp-profile-hero-pattern"></div>
</div>

<!-- PROFILE LAYOUT -->
<div class="asp-profile-wrap">

    <!-- ── SIDEBAR ── -->
    <div class="asp-sidebar">

        <!-- Photo Card -->
        <div class="asp-photo-card">
            <div class="asp-photo-wrap">
                @if($candidate->profile_picture)
                    <img src="{{ Storage::url($candidate->profile_picture) }}"
                         alt="{{ $candidate->name }}">
                @else
                    <div class="asp-photo-placeholder">
                        <span class="initials">
                            {{ strtoupper(substr($candidate->name, 0, 1)) }}{{ strtoupper(substr(strrchr($candidate->name, ' ') ?: '', 1, 1)) }}
                        </span>
                    </div>
                @endif
                <div class="asp-photo-overlay"></div>
                @if($candidate->position)
                    <div class="asp-photo-position">{{ $candidate->position->name }}</div>
                @endif
            </div>
            <div class="asp-photo-body">
                <div class="asp-candidate-name">{{ $candidate->name }}</div>
                @if($candidate->nick_name)
                    <div class="asp-candidate-nick">"{{ $candidate->nick_name }}"</div>
                @endif
                <div class="asp-photo-divider"></div>
                <div class="asp-location-pills">
                    @if($candidate->county)
                        <span class="asp-pill"><i class="fas fa-map-marker-alt"></i> {{ $candidate->county }}</span>
                    @endif
                    @if($candidate->constituency)
                        <span class="asp-pill"><i class="fas fa-circle" style="font-size:4px"></i> {{ $candidate->constituency }}</span>
                    @endif
                    @if($candidate->ward)
                        <span class="asp-pill"><i class="fas fa-circle" style="font-size:4px"></i> {{ $candidate->ward }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Details Card -->
        @if($candidate->bloc || $candidate->position)
        <div class="asp-info-card">
            <div class="asp-info-card-header">Details</div>
            @if($candidate->position)
            <div class="asp-info-row">
                <span class="asp-info-label">Running for</span>
                <span class="asp-info-value green">{{ $candidate->position->name }}</span>
            </div>
            @endif
            @if($candidate->bloc)
            <div class="asp-info-row">
                <span class="asp-info-label">Bloc</span>
                <span class="asp-info-value">{{ $candidate->bloc->name }}</span>
            </div>
            @endif
            @if($candidate->county)
            <div class="asp-info-row">
                <span class="asp-info-label">County</span>
                <span class="asp-info-value">{{ $candidate->county }}</span>
            </div>
            @endif
        </div>
        @endif

    </div>

    <!-- ── MAIN CONTENT ── -->
    <div class="asp-main">

        <!-- About -->
        @if($candidate->about)
        <div class="asp-section-card">
            <div class="asp-section-head">
                <div class="asp-section-head-bar"></div>
                <div class="asp-section-title">About {{ $candidate->name }}</div>
            </div>
            <div class="asp-section-body">
                <div class="asp-about-text">
                    {!! nl2br(e($candidate->about)) !!}
                </div>
            </div>
        </div>
        @endif

        <!-- Contact -->
        @if($candidate->phone || $candidate->email)
        <div class="asp-section-card">
            <div class="asp-section-head">
                <div class="asp-section-head-bar"></div>
                <div class="asp-section-title">Contact Information</div>
            </div>
            <div class="asp-section-body">
                <div class="asp-contact-grid">
                    @if($candidate->phone)
                    <div class="asp-contact-item">
                        <div class="asp-contact-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <div class="asp-contact-label">Phone</div>
                            <div class="asp-contact-value">{{ $candidate->phone }}</div>
                        </div>
                    </div>
                    @endif
                    @if($candidate->email)
                    <div class="asp-contact-item">
                        <div class="asp-contact-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <div class="asp-contact-label">Email</div>
                            <div class="asp-contact-value">{{ $candidate->email }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Related News -->
        @if(isset($relatedArticles) && $relatedArticles->count() > 0)
        <div class="asp-section-card">
            <div class="asp-section-head">
                <div class="asp-section-head-bar"></div>
                <div class="asp-section-title">Featured in News</div>
            </div>
            <div class="asp-section-body">
                <div class="asp-news-grid">
                    @foreach($relatedArticles as $article)
                    <a href="{{ route('news.public.show', $article->slug) }}" class="asp-news-card">
                        @if($article->featured_image)
                        <div class="asp-news-card-img">
                            <img src="{{ Storage::url($article->featured_image) }}"
                                 alt="{{ $article->title }}" loading="lazy">
                        </div>
                        @endif
                        <div class="asp-news-card-body">
                            <div class="asp-news-card-title">{{ $article->title }}</div>
                            <div class="asp-news-card-date">
                                <i class="fas fa-calendar-alt" style="font-size:9px;margin-right:4px"></i>
                                {{ $article->created_at->format('d M Y') }}
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection