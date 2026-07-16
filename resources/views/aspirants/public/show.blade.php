@extends('layouts.landing')
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap');

:root {
    --kenya-red: #BB0000;
    --kenya-black: #111111;
    --kenya-white: #F5F5F0;
    --green-bright: #00A86B;
    --kenya-green: #006600;
}

* { box-sizing: border-box; }
body { font-family: 'Barlow', sans-serif; background:#080808; color:var(--kenya-white); }
h1,h2,h3,h4 { font-family:'Oswald', sans-serif; }

.profile-page {
    min-height: 100vh;
    background:
        radial-gradient(900px 420px at 18% 18%, rgba(187,0,0,.16), transparent 60%),
        radial-gradient(900px 420px at 86% 12%, rgba(0,102,0,.18), transparent 62%),
        #080808;
}
.profile-shell { max-width: 1280px; margin: 0 auto; padding: 0 32px 80px; }
.profile-cover {
    position: relative;
    min-height: 420px;
    border-radius: 0 0 24px 24px;
    overflow: hidden;
    background: #141414;
    border: 1px solid rgba(255,255,255,.07);
    border-top: 0;
    box-shadow: 0 30px 90px rgba(0,0,0,.55);
}
.profile-cover img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.profile-cover-fallback {
    position:absolute; inset:0;
    background:
        linear-gradient(135deg, rgba(187,0,0,.48), rgba(0,102,0,.46)),
        repeating-linear-gradient(-45deg, rgba(255,255,255,.025) 0 1px, transparent 1px 42px),
        #121212;
}
.profile-cover::after {
    content:''; position:absolute; inset:0;
    background: linear-gradient(180deg, rgba(0,0,0,.05) 40%, rgba(8,8,8,.82) 100%);
}
.cover-label {
    position:absolute; right:28px; bottom:28px; z-index:2;
    display:inline-flex; align-items:center; gap:9px;
    padding:11px 15px; border-radius:12px;
    background:rgba(0,0,0,.58); border:1px solid rgba(255,255,255,.14);
    color:rgba(245,245,240,.86); font-size:13px; font-weight:700;
    backdrop-filter: blur(10px);
}
.profile-header-card {
    position: relative; z-index:3;
    margin: -86px 0 28px;
    min-height: 160px;
    display: grid;
    grid-template-columns: 190px 1fr auto;
    align-items: end;
    gap: 28px;
    padding: 0 32px 28px;
}
.profile-avatar {
    width: 190px; height: 190px;
    border-radius: 50%;
    border: 5px solid #101010;
    outline: 2px solid rgba(0,168,107,.75);
    overflow: hidden;
    background: #151515;
    box-shadow: 0 22px 55px rgba(0,0,0,.65);
    display:flex; align-items:center; justify-content:center;
}
.profile-avatar img { width:100%; height:100%; object-fit:cover; object-position:top center; }
.avatar-initials { font-family:'Oswald',sans-serif; font-size:64px; color:rgba(255,255,255,.22); }
.profile-identity { padding-bottom: 10px; }
.profile-name-row { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.profile-name { font-size:46px; line-height:1; font-weight:700; color:white; text-shadow:0 12px 30px rgba(0,0,0,.55); }
.verified-dot { width:28px; height:28px; border-radius:50%; background:var(--green-bright); color:white; display:grid; place-items:center; font-size:13px; }
.profile-subtitle { margin-top:10px; color:rgba(245,245,240,.68); font-size:17px; }
.profile-subtitle strong { color:var(--green-bright); font-weight:700; }
.profile-chips { margin-top:16px; display:flex; flex-wrap:wrap; gap:10px; }
.profile-chip { display:inline-flex; align-items:center; gap:8px; padding:9px 13px; border-radius:10px; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.045); color:rgba(245,245,240,.72); font-size:13px; }
.profile-chip i { color:var(--green-bright); }
.profile-actions { display:flex; gap:12px; padding-bottom:12px; }
.profile-action { display:inline-flex; align-items:center; justify-content:center; gap:9px; min-width:118px; padding:14px 18px; border-radius:12px; text-decoration:none; font-weight:800; color:white; border:1px solid rgba(255,255,255,.13); background:rgba(255,255,255,.05); }
.profile-action.primary { background:linear-gradient(135deg,#00A86B,#007a4f); border-color:rgba(0,168,107,.7); }
.profile-action:hover { transform: translateY(-1px); border-color:rgba(0,168,107,.45); }
.profile-content { display:grid; grid-template-columns: 340px 1fr; gap:28px; }
.profile-card { background:rgba(20,20,20,.86); border:1px solid rgba(255,255,255,.075); border-radius:20px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.26); }
.profile-card-head { display:flex; align-items:center; gap:12px; padding:22px 24px; border-bottom:1px solid rgba(255,255,255,.06); }
.profile-card-head .bar { width:4px; height:28px; border-radius:99px; background:linear-gradient(180deg,var(--kenya-red),var(--kenya-green)); }
.profile-card-title { font-size:22px; color:white; font-weight:700; }
.profile-card-body { padding:22px 24px 26px; }
.summary-list { display:flex; flex-direction:column; gap:14px; }
.summary-item { display:flex; gap:13px; align-items:flex-start; padding-bottom:14px; border-bottom:1px solid rgba(255,255,255,.055); }
.summary-item:last-child { padding-bottom:0; border-bottom:0; }
.summary-icon { width:38px; height:38px; border-radius:12px; background:rgba(0,168,107,.12); border:1px solid rgba(0,168,107,.18); display:grid; place-items:center; color:var(--green-bright); flex-shrink:0; }
.summary-label { font-size:12px; color:rgba(245,245,240,.36); margin-bottom:3px; }
.summary-value { font-size:14px; color:var(--kenya-white); font-weight:700; overflow-wrap:anywhere; }
.about-text { color:rgba(245,245,240,.68); font-size:16px; line-height:1.85; }
.priority-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
.priority { padding:18px; border:1px solid rgba(255,255,255,.075); border-radius:16px; background:rgba(255,255,255,.035); color:rgba(245,245,240,.72); font-weight:700; font-size:13px; }
.priority i { display:block; color:var(--green-bright); font-size:22px; margin-bottom:12px; }
.news-list { display:grid; gap:14px; }
.news-row { display:flex; gap:14px; align-items:center; padding:12px; border:1px solid rgba(255,255,255,.07); border-radius:15px; color:inherit; text-decoration:none; background:rgba(255,255,255,.03); }
.news-row:hover { border-color:rgba(0,168,107,.35); }
.news-thumb { width:86px; height:64px; border-radius:11px; overflow:hidden; flex-shrink:0; background:#101010; }
.news-thumb img { width:100%; height:100%; object-fit:cover; }
.news-title { font-family:'Oswald',sans-serif; color:white; font-size:16px; line-height:1.25; }
.news-date { margin-top:5px; color:rgba(245,245,240,.35); font-size:12px; }
.empty-note { color:rgba(245,245,240,.4); font-size:14px; }

@media (max-width: 980px) {
    .profile-shell { padding:0 16px 56px; }
    .profile-cover { min-height:300px; }
    .profile-header-card { grid-template-columns:1fr; margin-top:-70px; padding:0 18px 24px; gap:14px; }
    .profile-avatar { width:150px; height:150px; }
    .profile-name { font-size:34px; }
    .profile-actions { flex-wrap:wrap; }
    .profile-content { grid-template-columns:1fr; }
    .priority-grid { grid-template-columns:1fr 1fr; }
}
@media (max-width: 560px) {
    .profile-cover { min-height:240px; }
    .profile-avatar { width:128px; height:128px; }
    .profile-actions { flex-direction:column; }
    .profile-action { width:100%; }
    .priority-grid { grid-template-columns:1fr; }
}
</style>

@include('components.frontend-nav')

@php
    $relatedArticles = $candidate->relatedArticles ?? collect();
    $initials = strtoupper(substr($candidate->name, 0, 1)) . strtoupper(substr(strrchr($candidate->name, ' ') ?: '', 1, 1));
    $positionLabel = $candidate->position?->name;
    $partyLabel = $candidate->politicalParty?->abbreviation ?: $candidate->politicalParty?->name;
@endphp

<div class="profile-page">
    <div class="profile-shell">
        <div class="profile-cover">
            @if($candidate->cover_photo)
                <img src="{{ Storage::url($candidate->cover_photo) }}" alt="{{ $candidate->name }} cover photo">
            @else
                <div class="profile-cover-fallback"></div>
            @endif
            <div class="cover-label"><i class="fas fa-camera"></i> Cover photo</div>
        </div>

        <section class="profile-header-card">
            <div class="profile-avatar">
                @if($candidate->profile_picture)
                    <img src="{{ Storage::url($candidate->profile_picture) }}" alt="{{ $candidate->name }}">
                @else
                    <span class="avatar-initials">{{ $initials }}</span>
                @endif
            </div>

            <div class="profile-identity">
                <div class="profile-name-row">
                    <h1 class="profile-name">{{ $candidate->name }}</h1>
                    @if($candidate->approval_status === 'approved')
                        <span class="verified-dot"><i class="fas fa-check"></i></span>
                    @endif
                </div>
                <div class="profile-subtitle">
                    {{ $positionLabel ?? 'Aspirant' }}@if($candidate->county), {{ $candidate->county }}@endif
                    @if($partyLabel)<span>&nbsp;&bull;&nbsp;</span><strong>{{ $partyLabel }}</strong>@endif
                </div>
                <div class="profile-chips">
                    @if($candidate->county)<span class="profile-chip"><i class="fas fa-map-marker-alt"></i>{{ $candidate->county }}</span>@endif
                    @if($candidate->constituency)<span class="profile-chip"><i class="fas fa-flag"></i>{{ $candidate->constituency }}</span>@endif
                    @if($candidate->ward)<span class="profile-chip"><i class="fas fa-location-dot"></i>{{ $candidate->ward }}</span>@endif
                    @if($candidate->politicalParty)<span class="profile-chip"><i class="fas fa-landmark"></i>{{ $candidate->politicalParty->name }}</span>@endif
                </div>
            </div>

            <div class="profile-actions">
                @if($candidate->phone)<a class="profile-action primary" href="tel:{{ $candidate->phone }}"><i class="fas fa-heart"></i> Support</a>@endif
                <a class="profile-action" href="{{ route('aspirants.public') }}"><i class="fas fa-arrow-left"></i> Aspirants</a>
                @if($candidate->email)<a class="profile-action" href="mailto:{{ $candidate->email }}"><i class="fas fa-envelope"></i> Contact</a>@endif
            </div>
        </section>

        <div class="profile-content">
            <aside class="profile-card">
                <div class="profile-card-head"><span class="bar"></span><div class="profile-card-title">Profile Summary</div></div>
                <div class="profile-card-body summary-list">
                    @if($positionLabel)<div class="summary-item"><span class="summary-icon"><i class="fas fa-briefcase"></i></span><div><div class="summary-label">Position</div><div class="summary-value">{{ $positionLabel }}</div></div></div>@endif
                    @if($candidate->politicalParty)<div class="summary-item"><span class="summary-icon"><i class="fas fa-flag"></i></span><div><div class="summary-label">Party</div><div class="summary-value">{{ $candidate->politicalParty->name }}</div></div></div>@endif
                    @if($candidate->county)<div class="summary-item"><span class="summary-icon"><i class="fas fa-map"></i></span><div><div class="summary-label">County</div><div class="summary-value">{{ $candidate->county }}</div></div></div>@endif
                    @if($candidate->constituency)<div class="summary-item"><span class="summary-icon"><i class="fas fa-location-crosshairs"></i></span><div><div class="summary-label">Constituency</div><div class="summary-value">{{ $candidate->constituency }}</div></div></div>@endif
                    @if($candidate->ward)<div class="summary-item"><span class="summary-icon"><i class="fas fa-location-dot"></i></span><div><div class="summary-label">Ward</div><div class="summary-value">{{ $candidate->ward }}</div></div></div>@endif
                    @if($candidate->phone)<div class="summary-item"><span class="summary-icon"><i class="fas fa-phone"></i></span><div><div class="summary-label">Phone</div><div class="summary-value">{{ $candidate->phone }}</div></div></div>@endif
                    @if($candidate->email)<div class="summary-item"><span class="summary-icon"><i class="fas fa-envelope"></i></span><div><div class="summary-label">Email</div><div class="summary-value">{{ $candidate->email }}</div></div></div>@endif
                </div>
            </aside>

            <main style="display:flex;flex-direction:column;gap:22px;">
                <section class="profile-card">
                    <div class="profile-card-head"><span class="bar"></span><div class="profile-card-title">About {{ $candidate->name }}</div></div>
                    <div class="profile-card-body">
                        @if($candidate->about)
                            <div class="about-text">{!! nl2br(e($candidate->about)) !!}</div>
                        @else
                            <div class="empty-note">No biography has been added yet.</div>
                        @endif
                    </div>
                </section>

                <section class="profile-card">
                    <div class="profile-card-head"><span class="bar"></span><div class="profile-card-title">Campaign Priorities</div></div>
                    <div class="profile-card-body priority-grid">
                        <div class="priority"><i class="fas fa-seedling"></i>Community Development</div>
                        <div class="priority"><i class="fas fa-briefcase"></i>Jobs & Empowerment</div>
                        <div class="priority"><i class="fas fa-graduation-cap"></i>Education & Youth</div>
                        <div class="priority"><i class="fas fa-shield-halved"></i>Accountable Leadership</div>
                    </div>
                </section>

                @if($relatedArticles->count() > 0)
                <section class="profile-card">
                    <div class="profile-card-head"><span class="bar"></span><div class="profile-card-title">Latest Updates</div></div>
                    <div class="profile-card-body news-list">
                        @foreach($relatedArticles as $article)
                            <a href="{{ route('news.public.show', $article->slug) }}" class="news-row">
                                <div class="news-thumb">
                                    @if($article->featured_image)
                                        <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->title }}">
                                    @endif
                                </div>
                                <div>
                                    <div class="news-title">{{ $article->title }}</div>
                                    <div class="news-date">{{ $article->created_at->format('d M Y') }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
                @endif
            </main>
        </div>
    </div>
</div>

@endsection
