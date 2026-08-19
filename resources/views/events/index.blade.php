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

body { font-family: 'Barlow', sans-serif; background: #0a0a0a; color: var(--kenya-white); }
h1,h2,h3,h4 { font-family: 'Oswald', sans-serif; }

.flag-stripe {
    height: 4px;
    background: linear-gradient(90deg, var(--kenya-green) 33%, #1a1a1a 33% 66%, var(--kenya-red) 66%);
}

/* ── HERO ── */
.events-hero {
    position: relative;
    padding: 72px 32px 56px;
    text-align: center;
    overflow: hidden;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.events-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 60% 100% at 20% 60%, rgba(0,102,0,0.12) 0%, transparent 60%),
        radial-gradient(ellipse 60% 100% at 80% 40%, rgba(187,0,0,0.12) 0%, transparent 60%);
    pointer-events: none;
}
.events-hero-pattern {
    position: absolute; inset: 0; pointer-events: none;
    background-image: repeating-linear-gradient(
        -45deg, transparent, transparent 40px,
        rgba(255,255,255,0.01) 40px, rgba(255,255,255,0.01) 41px
    );
}
.events-hero h1 {
    font-size: clamp(44px, 6vw, 72px);
    font-weight: 700; line-height: 0.95;
    letter-spacing: -1px; margin-bottom: 16px;
    position: relative;
}
.events-hero h1 em {
    font-style: normal;
    background: linear-gradient(135deg, var(--green-bright), #66ffcc);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.events-hero p {
    font-size: 17px; color: rgba(245,245,240,0.45);
    max-width: 520px; margin: 0 auto;
    position: relative;
}

/* ── LAYOUT ── */
.events-container {
    max-width: 1280px; margin: 0 auto;
    padding: 48px 32px 80px;
}

.events-section-title {
    font-size: 28px; font-weight: 700;
    letter-spacing: 1px; color: white;
    margin-bottom: 32px; display: flex; align-items: center; gap: 12px;
}
.events-section-title::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(90deg, rgba(255,255,255,0.1), transparent);
}

/* ── GRID & CARDS ── */
.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 28px;
    margin-bottom: 64px;
}

.event-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 24px; overflow: hidden;
    display: flex; flex-direction: column;
    min-height: 380px;
    transition: all 0.3s ease;
    position: relative;
}
.event-card:hover {
    border-color: rgba(0, 168, 107, 0.4);
    transform: translateY(-5px);
    box-shadow: 0 24px 60px rgba(0,0,0,0.6);
}

.event-card-header {
    padding: 24px 28px 12px;
    position: relative;
}
.event-badge {
    display: inline-block;
    padding: 5px 12px; border-radius: 8px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    background: rgba(0, 168, 107, 0.1);
    color: var(--green-bright);
    border: 1px solid rgba(0, 168, 107, 0.2);
}
.event-date {
    font-size: 13px; color: rgba(245,245,240,0.4);
    margin-top: 14px; display: flex; align-items: center; gap: 8px;
}

.event-card-body {
    padding: 12px 28px 24px;
    flex: 1;
    display: flex; flex-direction: column;
}
.event-title {
    font-size: 22px; font-weight: 600; line-height: 1.25;
    color: white; text-decoration: none;
    margin-bottom: 12px;
    transition: color 0.2s;
}
.event-card:hover .event-title {
    color: var(--green-bright);
}
.event-description {
    font-size: 14px; line-height: 1.6;
    color: rgba(245,245,240,0.45);
    margin-bottom: 20px;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
    overflow: hidden;
}

.event-meta {
    margin-top: auto;
    padding-top: 18px; border-top: 1px solid rgba(255,255,255,0.05);
    display: flex; align-items: center; justify-content: space-between;
}
.event-location {
    font-size: 13px; color: rgba(245,245,240,0.5);
    display: flex; align-items: center; gap: 6px;
}
.event-price {
    font-size: 15px; font-weight: 700; color: white;
}

.event-card-action {
    display: block; width: 100%; text-align: center;
    background: rgba(255,255,255,0.04);
    border-top: 1px solid rgba(255,255,255,0.06);
    padding: 16px; font-size: 14px; font-weight: 600;
    color: var(--green-bright); text-decoration: none;
    transition: all 0.2s;
}
.event-card:hover .event-card-action {
    background: var(--green-bright); color: white;
}

/* ── PAST EVENTS ── */
.past-events-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
    opacity: 0.65;
}
.past-event-card {
    background: #111;
    border: 1px solid rgba(255,255,255,0.04);
    border-radius: 20px; padding: 24px;
}

@media (max-width: 768px) {
    .events-hero { padding: 56px 16px 44px; }
    .events-container { padding: 32px 16px 60px; }
    .events-grid { grid-template-columns: 1fr; }
}
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')

<!-- HERO -->
<div class="events-hero">
    <div class="events-hero-pattern"></div>
    <h1>Events &amp; <em>Forums</em></h1>
    <p>Engage, connect, and participate in assemblies shaping Kenya's political landscape.</p>
</div>

<div class="events-container">
    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl p-4 mb-8 max-w-4xl mx-auto flex items-center gap-3">
            <i class="fas fa-check-circle text-lg"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-2xl p-4 mb-8 max-w-4xl mx-auto flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-lg"></i>
            <div>{{ session('warning') }}</div>
        </div>
    @endif

    <!-- UPCOMING EVENTS -->
    <h2 class="events-section-title">
        <i class="fas fa-calendar-star text-green-bright"></i> Upcoming Events
    </h2>

    <div class="events-grid">
        @forelse($upcomingEvents as $event)
            <div class="event-card">
                <div class="event-card-header">
                    <span class="event-badge">Upcoming</span>
                    <div class="event-date">
                        <i class="far fa-calendar-alt text-emerald-500"></i>
                        {{ $event->date->format('l, F d, Y \a\t h:i A') }}
                    </div>
                </div>
                <div class="event-card-body">
                    <a href="{{ route('events.show', $event->slug) }}" class="event-title">
                        {{ $event->title }}
                    </a>
                    <p class="event-description">
                        {{ Str::limit(strip_tags($event->description), 140) }}
                    </p>
                    <div class="event-meta">
                        <span class="event-location">
                            <i class="fas fa-map-marker-alt text-zinc-500"></i>
                            {{ $event->location }}
                        </span>
                        <span class="event-price">
                            KES {{ number_format($event->price) }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('events.show', $event->slug) }}" class="event-card-action">
                    Register &amp; Book Seat <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-zinc-500 bg-zinc-900/40 rounded-3xl border border-zinc-800">
                <i class="fas fa-calendar-times text-4xl mb-4 opacity-40"></i>
                <h3 class="text-xl font-semibold text-zinc-400">No upcoming events scheduled</h3>
                <p class="text-sm text-zinc-650 mt-1">Please inspect past events or check back shortly for updates.</p>
            </div>
        @endif
    </div>

    <!-- PAST EVENTS -->
    @if($pastEvents->count() > 0)
        <h2 class="events-section-title">
            <i class="fas fa-history text-zinc-500"></i> Past Events
        </h2>
        <div class="past-events-list">
            @foreach($pastEvents as $event)
                <div class="past-event-card">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-semibold text-zinc-500 uppercase tracking-widest">Concluded</span>
                        <span class="text-xs text-zinc-400 font-mono">{{ $event->date->format('M d, Y') }}</span>
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-300 mb-2">{{ $event->title }}</h3>
                    <p class="text-sm text-zinc-500 mb-3">{{ Str::limit(strip_tags($event->description), 100) }}</p>
                    <div class="text-xs text-zinc-600 flex items-center gap-1">
                        <i class="fas fa-map-marker-alt"></i> {{ $event->location }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
