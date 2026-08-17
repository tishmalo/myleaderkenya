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
.cover-edit-action {
    position:absolute; right:28px; top:24px; z-index:3;
    display:inline-flex; align-items:center; gap:8px; min-height:36px; padding:0 12px;
    border:1px solid rgba(255,255,255,.16); border-radius:8px;
    background:rgba(0,0,0,.68); color:white; font-size:12px; font-weight:900;
    text-transform:uppercase; letter-spacing:.04em; cursor:pointer;
}
.cover-edit-action:hover { border-color:rgba(0,168,107,.5); color:var(--green-bright); }
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
.social-links { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
.social-link { display:flex; align-items:center; gap:10px; min-height:42px; padding:0 13px; border:1px solid rgba(255,255,255,.08); border-radius:10px; background:rgba(255,255,255,.035); color:rgba(245,245,240,.78); text-decoration:none; font-weight:900; font-size:13px; }
.social-link i { color:var(--green-bright); width:18px; text-align:center; font-size:16px; }
.social-link:hover { border-color:rgba(0,168,107,.4); color:white; }
.about-text { color:rgba(245,245,240,.68); font-size:16px; line-height:1.85; }
.priority-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
.priority { padding:18px; border:1px solid rgba(255,255,255,.075); border-radius:16px; background:rgba(255,255,255,.035); color:rgba(245,245,240,.72); font-weight:700; font-size:13px; }
.priority i { display:block; color:var(--green-bright); font-size:22px; margin-bottom:12px; }
.priority strong { display:block; color:white; font-size:16px; }
.priority p { overflow-wrap:anywhere; margin:9px 0 0; color:rgba(245,245,240,.58); font-weight:400; font-size:14px; line-height:1.6; white-space:pre-line; }
.parliament-overview { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
.parliament-stat { border:1px solid rgba(255,255,255,.075); border-radius:14px; background:rgba(255,255,255,.03); padding:16px; }
.parliament-stat span { display:block; color:rgba(245,245,240,.4); font-size:11px; text-transform:uppercase; letter-spacing:.08em; }
.parliament-stat strong { display:block; margin-top:6px; color:white; font-size:18px; }
.parliament-biography { margin-top:18px; color:rgba(245,245,240,.68); line-height:1.75; }
.parliament-subsection { margin-top:22px; }
.parliament-subsection h3 { margin-bottom:12px; color:white; font-size:18px; }
.parliament-tags { display:flex; flex-wrap:wrap; gap:8px; }
.parliament-tag { border:1px solid rgba(0,168,107,.25); border-radius:999px; background:rgba(0,168,107,.08); padding:8px 12px; color:#a7f3d0; font-size:12px; }
.parliament-activity-list { display:grid; gap:10px; }
.parliament-activity { display:grid; grid-template-columns:90px 1fr auto; gap:12px; align-items:center; border:1px solid rgba(255,255,255,.07); border-radius:13px; background:rgba(255,255,255,.025); padding:13px; }
.parliament-activity-type { color:var(--green-bright); font-size:11px; font-weight:900; text-transform:uppercase; }
.parliament-activity-title { color:rgba(245,245,240,.82); font-weight:700; }
.parliament-activity-meta { color:rgba(245,245,240,.42); font-size:12px; }
.news-list { display:grid; gap:14px; }
.news-row { display:flex; gap:14px; align-items:center; padding:12px; border:1px solid rgba(255,255,255,.07); border-radius:15px; color:inherit; text-decoration:none; background:rgba(255,255,255,.03); }
.news-row:hover { border-color:rgba(0,168,107,.35); }
.news-thumb { width:86px; height:64px; border-radius:11px; overflow:hidden; flex-shrink:0; background:#101010; }
.news-thumb img { width:100%; height:100%; object-fit:cover; }
.news-title { font-family:'Oswald',sans-serif; color:white; font-size:16px; line-height:1.25; }
.news-date { margin-top:5px; color:rgba(245,245,240,.35); font-size:12px; }
.empty-note { color:rgba(245,245,240,.4); font-size:14px; }
.profile-cover-modal { position:fixed; inset:0; z-index:10040; display:none; align-items:center; justify-content:center; padding:20px; background:rgba(0,0,0,.76); backdrop-filter:blur(8px); }
.profile-cover-modal.is-open { display:flex; }
.profile-cover-dialog { width:min(560px,100%); border:1px solid rgba(255,255,255,.12); border-radius:8px; background:#111; padding:22px; box-shadow:0 24px 70px rgba(0,0,0,.58); }
.profile-cover-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:16px; }
.profile-cover-title { margin:0; font-family:'Oswald',sans-serif; font-size:26px; line-height:1.1; }
.profile-cover-note { margin:6px 0 0; color:rgba(245,245,240,.58); font-size:13px; line-height:1.45; }
.profile-cover-close { width:36px; height:36px; border-radius:8px; border:1px solid rgba(255,255,255,.12); background:#151515; color:#fff; cursor:pointer; }
.profile-cover-form { display:grid; gap:14px; }
.profile-cover-preview { aspect-ratio:16/7; overflow:hidden; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:#171717; display:grid; place-items:center; color:rgba(245,245,240,.48); }
.profile-cover-preview img { width:100%; height:100%; object-fit:cover; display:block; }
.profile-cover-form label { display:grid; gap:7px; color:rgba(245,245,240,.68); font-size:12px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
.profile-cover-form input[type=file] { width:100%; border:1px dashed rgba(255,255,255,.18); border-radius:8px; background:#0b0b0b; color:rgba(245,245,240,.72); padding:13px; font:inherit; }
.profile-cover-submit { min-height:44px; border:0; border-radius:8px; background:#006600; color:#fff; font-weight:900; cursor:pointer; }
.profile-flash { margin:18px 0 0; padding:14px 16px; border-radius:12px; border:1px solid rgba(0,168,107,.35); background:rgba(0,168,107,.1); color:#d8fff0; font-weight:700; }
.profile-flash.error { border-color:rgba(187,0,0,.4); background:rgba(187,0,0,.14); color:#ffd9d9; }
.campaign-media-grid { display:grid; grid-template-columns:minmax(0,2fr) minmax(250px,1fr); gap:14px; align-items:start; }
.campaign-media-grid > :only-child { grid-column:1 / -1; }
.campaign-media-video, .campaign-media-item { overflow:hidden; border:1px solid rgba(255,255,255,.09); border-radius:13px; background:#111; }
.campaign-media-label { display:flex; align-items:center; gap:8px; min-height:40px; padding:9px 12px; color:#fff; font-family:'Oswald',sans-serif; font-size:14px; font-weight:700; }
.campaign-media-label i { color:var(--green-bright); }
.campaign-media-label .fa-youtube { color:#ff3434; }
.campaign-media-frame { display:block; width:100%; aspect-ratio:16/9; border:0; background:#000; }
.campaign-media-side { display:grid; gap:12px; }
.campaign-media-audio { padding:0 12px 12px; }
.campaign-media-audio audio { display:block; width:100%; height:38px; }
.campaign-poster-row { display:grid; grid-template-columns:74px minmax(0,1fr); gap:12px; align-items:center; padding:0 12px 12px; }
.campaign-poster-thumb { width:74px; aspect-ratio:3/4; border-radius:8px; object-fit:cover; background:#080808; }
.campaign-poster-open { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:38px; border:1px solid rgba(0,168,107,.35); border-radius:8px; background:rgba(0,168,107,.09); color:#5ee7a8; font-weight:800; cursor:pointer; }
.campaign-poster-open:hover { border-color:var(--green-bright); color:#fff; }
.campaign-poster-modal { position:fixed; inset:0; z-index:10040; display:none; align-items:center; justify-content:center; padding:24px; background:rgba(0,0,0,.88); backdrop-filter:blur(8px); }
.campaign-poster-modal.is-open { display:flex; }
.campaign-poster-dialog { position:relative; max-width:min(760px,100%); max-height:calc(100vh - 48px); }
.campaign-poster-dialog img { display:block; max-width:100%; max-height:calc(100vh - 48px); border-radius:14px; box-shadow:0 30px 90px rgba(0,0,0,.7); }
.campaign-poster-close { position:absolute; top:10px; right:10px; display:grid; place-items:center; width:40px; height:40px; border:1px solid rgba(255,255,255,.18); border-radius:9px; background:rgba(0,0,0,.78); color:#fff; cursor:pointer; }

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
    .priority-grid, .parliament-overview { grid-template-columns:1fr; }
    .parliament-activity { grid-template-columns:1fr; }
    .campaign-media-grid { grid-template-columns:1fr; }
    .campaign-poster-row { grid-template-columns:64px minmax(0,1fr); }
    .campaign-poster-thumb { width:64px; }
}
</style>

@include('components.frontend-nav')

@php
    $relatedArticles = $candidate->relatedArticles ?? collect();
    $parliamentMember = $candidate->parliamentMember;
    $initials = strtoupper(substr($candidate->name, 0, 1)) . strtoupper(substr(strrchr($candidate->name, ' ') ?: '', 1, 1));
    $positionLabel = $candidate->position?->name;
    $partyLabel = $candidate->politicalParty?->abbreviation ?: $candidate->politicalParty?->name;
    $maskedPhone = $candidate->maskedPhone();
    $maskedEmail = $candidate->maskedEmail();
    $canEditCoverPhoto = auth()->check() && auth()->id() === $candidate->user_id && Route::has('aspirant.cover-photo.update');
    $youtubeVideoId = function (?string $url): ?string {
        if (blank($url)) return null;

        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $videoId = null;

        if ($host === 'youtu.be' || $host === 'www.youtu.be') {
            $videoId = explode('/', $path)[0] ?? null;
        } elseif ($host === 'youtube.com' || $host === 'www.youtube.com' || $host === 'm.youtube.com') {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $pathParts = array_values(array_filter(explode('/', $path)));
            $videoId = $query['v'] ?? null;

            if (! $videoId && in_array($pathParts[0] ?? null, ['embed', 'shorts', 'live'], true)) {
                $videoId = $pathParts[1] ?? null;
            }
        }

        return is_string($videoId) && preg_match('/^[A-Za-z0-9_-]{6,20}$/', $videoId)
            ? $videoId
            : null;
    };
    $campaignVideoId = $youtubeVideoId($candidate->campaign_video_url);
    $campaignSongId = $youtubeVideoId($candidate->campaign_song_url);
    $hasCampaignMedia = $campaignVideoId
        || $campaignSongId
        || filled($candidate->campaign_skiza_audio)
        || filled($candidate->campaign_poster);    $socialLinks = collect([
        ['label' => 'Facebook', 'url' => $candidate->facebook_url, 'icon' => 'fa-brands fa-facebook-f'],
        ['label' => 'X', 'url' => $candidate->x_url, 'icon' => 'fa-brands fa-x-twitter'],
        ['label' => 'Instagram', 'url' => $candidate->instagram_url, 'icon' => 'fa-brands fa-instagram'],
        ['label' => 'TikTok', 'url' => $candidate->tiktok_url, 'icon' => 'fa-brands fa-tiktok'],
        ['label' => 'YouTube', 'url' => $candidate->youtube_url, 'icon' => 'fa-brands fa-youtube'],
        ['label' => 'WhatsApp Group', 'url' => $candidate->whatsapp_group_url, 'icon' => 'fa-brands fa-whatsapp'],
    ])->filter(fn ($link) => filled($link['url']))->values();
@endphp

<div class="profile-page">
    <div class="profile-shell">
        @if(session('success'))
            <div class="profile-flash">{{ session('success') }}</div>
        @endif
        <div class="profile-cover">
            @if($candidate->cover_photo)
                <img src="{{ Storage::url($candidate->cover_photo) }}" alt="{{ $candidate->name }} cover photo">
            @else
                <div class="profile-cover-fallback"></div>
            @endif
            <div class="cover-label"><i class="fas fa-camera"></i> Cover photo</div>
            @if($canEditCoverPhoto)
                <button type="button" class="cover-edit-action" data-public-cover-open>
                    <i class="fas fa-camera"></i> {{ $candidate->cover_photo ? 'Edit Cover' : 'Add Cover' }}
                </button>
            @endif
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
                <a href="{{ route('aspirants.register', ['candidate_id' => $candidate->id, 'submission_mode' => 'adoption']) }}" class="profile-action primary" data-aspirant-register-popup data-aspirant-register-title="Support Aspirant"><i class="fas fa-heart"></i> Support</a>
                <a href="{{ route('aspirants.register', ['candidate_id' => $candidate->id]) }}" class="profile-action" data-aspirant-register-popup data-aspirant-register-title="Claim Profile"><i class="fas fa-user-check"></i> Claim Profile</a>
                <a class="profile-action" href="{{ route('aspirants.public') }}"><i class="fas fa-arrow-left"></i> Aspirants</a>
                @if($maskedEmail)<span class="profile-action"><i class="fas fa-envelope"></i> Contact</span>@endif
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
                    @if($maskedPhone)<div class="summary-item"><span class="summary-icon"><i class="fas fa-phone"></i></span><div><div class="summary-label">Phone</div><div class="summary-value">{{ $maskedPhone }}</div></div></div>@endif
                    @if($maskedEmail)<div class="summary-item"><span class="summary-icon"><i class="fas fa-envelope"></i></span><div><div class="summary-label">Email</div><div class="summary-value">{{ $maskedEmail }}</div></div></div>@endif
                </div>
            </aside>

            <main style="display:flex;flex-direction:column;gap:22px;">
                @if($socialLinks->isNotEmpty())
                <section class="profile-card">
                    <div class="profile-card-head"><span class="bar"></span><div class="profile-card-title">Social Media</div></div>
                    <div class="profile-card-body social-links">
                        @foreach($socialLinks as $link)
                            <a href="{{ $link['url'] }}" class="social-link" target="_blank" rel="noopener noreferrer">
                                <i class="{{ $link['icon'] }}"></i>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
                @endif

                @if($hasCampaignMedia)
                <section class="profile-card">
                    <div class="profile-card-head"><span class="bar"></span><div class="profile-card-title">Campaign Media</div></div>
                    <div class="profile-card-body campaign-media-grid">
                        @if($campaignVideoId)
                            <div class="campaign-media-video">
                                <div class="campaign-media-label"><i class="fa-brands fa-youtube"></i> Campaign Video</div>
                                <iframe
                                    class="campaign-media-frame"
                                    src="https://www.youtube.com/embed/{{ $campaignVideoId }}?mute=1&playsinline=1&rel=0"
                                    title="{{ $candidate->name }} campaign video"
                                    loading="lazy"
                                    allow="autoplay; encrypted-media; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        @endif

                        @if($campaignSongId || $candidate->campaign_skiza_audio || $candidate->campaign_poster)
                            <div class="campaign-media-side">
                                @if($campaignSongId)
                                    <div class="campaign-media-item">
                                        <div class="campaign-media-label"><i class="fa-brands fa-youtube"></i> Campaign Song</div>
                                        <iframe
                                            class="campaign-media-frame"
                                            src="https://www.youtube.com/embed/{{ $campaignSongId }}?mute=1&playsinline=1&rel=0"
                                            title="{{ $candidate->name }} campaign song"
                                            loading="lazy"
                                            allow="autoplay; encrypted-media; picture-in-picture"
                                            allowfullscreen
                                        ></iframe>
                                    </div>
                                @endif

                                @if($candidate->campaign_skiza_audio)
                                    <div class="campaign-media-item">
                                        <div class="campaign-media-label"><i class="fas fa-volume-high"></i> Skiza Tune</div>
                                        <div class="campaign-media-audio">
                                            <audio controls preload="metadata" src="{{ Storage::url($candidate->campaign_skiza_audio) }}">Your browser does not support audio playback.</audio>
                                        </div>
                                    </div>
                                @endif

                                @if($candidate->campaign_poster)
                                    <div class="campaign-media-item">
                                        <div class="campaign-media-label"><i class="fas fa-image"></i> Campaign Poster</div>
                                        <div class="campaign-poster-row">
                                            <img src="{{ Storage::url($candidate->campaign_poster) }}" alt="{{ $candidate->name }} campaign poster" class="campaign-poster-thumb" loading="lazy">
                                            <button type="button" class="campaign-poster-open" data-campaign-poster-open><i class="fas fa-eye"></i> View Poster</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </section>

                @if($candidate->campaign_poster)
                <div class="campaign-poster-modal" data-campaign-poster-modal aria-hidden="true">
                    <div class="campaign-poster-dialog" role="dialog" aria-modal="true" aria-label="Campaign poster">
                        <img src="{{ Storage::url($candidate->campaign_poster) }}" alt="{{ $candidate->name }} campaign poster">
                        <button type="button" class="campaign-poster-close" data-campaign-poster-close aria-label="Close campaign poster"><i class="fas fa-xmark"></i></button>
                    </div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const modal = document.querySelector('[data-campaign-poster-modal]');
                    const openButton = document.querySelector('[data-campaign-poster-open]');
                    const closeButton = document.querySelector('[data-campaign-poster-close]');
                    const closePoster = () => {
                        modal?.classList.remove('is-open');
                        modal?.setAttribute('aria-hidden', 'true');
                    };

                    openButton?.addEventListener('click', () => {
                        modal?.classList.add('is-open');
                        modal?.setAttribute('aria-hidden', 'false');
                    });
                    closeButton?.addEventListener('click', closePoster);
                    modal?.addEventListener('click', (event) => {
                        if (event.target === modal) closePoster();
                    });
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') closePoster();
                    });
                });
                </script>
                @endif
                @endif

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

                @php($publishedPriorities = $candidate->campaignPriorities->filter(fn ($priority) => $priority->category)->sortBy(fn ($priority) => $priority->category->sort_order))
                @if($publishedPriorities->isNotEmpty())
                <section class="profile-card">
                    <div class="profile-card-head"><span class="bar"></span><div class="profile-card-title">Campaign Priorities</div></div>
                    <div class="profile-card-body priority-grid">
                        @foreach($publishedPriorities as $priority)
                            <article class="priority">
                                <i class="{{ $priority->category->icon }}"></i>
                                <strong>{{ $priority->category->name }}</strong>
                                <p>{{ $priority->manifesto }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
                @endif


                @if($parliamentMember)
                <section class="profile-card">
                    <div class="profile-card-head"><span class="bar"></span><div class="profile-card-title">Parliamentary Record</div></div>
                    <div class="profile-card-body">
                        <div class="parliament-overview">
                            <div class="parliament-stat"><span>House</span><strong>{{ ucfirst($parliamentMember->house ?: 'Not supplied') }}</strong></div>
                            <div class="parliament-stat"><span>Constituency / Role</span><strong>{{ $parliamentMember->constituency ?: ($parliamentMember->role ?: 'Not supplied') }}</strong></div>
                            <div class="parliament-stat"><span>Party</span><strong>{{ $parliamentMember->party ?: 'Not supplied' }}</strong></div>
                            <div class="parliament-stat"><span>Recorded activity</span><strong>{{ number_format($parliamentMember->activities->count()) }}</strong></div>
                            <div class="parliament-stat"><span>Speeches</span><strong>{{ $parliamentMember->speeches_total === null ? 'Not supplied' : number_format($parliamentMember->speeches_total) }}</strong></div>
                            <div class="parliament-stat"><span>Bills</span><strong>{{ $parliamentMember->bills_total === null ? 'Not supplied' : number_format($parliamentMember->bills_total) }}</strong></div>
                        </div>
                        @if($parliamentMember->biography)<div class="parliament-biography">{!! nl2br(e($parliamentMember->biography)) !!}</div>@endif
                        @if($parliamentMember->committees->isNotEmpty())<div class="parliament-subsection"><h3>Committee Service</h3><div class="parliament-tags">@foreach($parliamentMember->committees as $committee)<span class="parliament-tag"><i class="fas fa-people-roof mr-1"></i>{{ $committee->name }}</span>@endforeach</div></div>@endif
                        @if($parliamentMember->activities->isNotEmpty())<div class="parliament-subsection"><h3>Parliamentary Activity</h3><div class="parliament-activity-list">@foreach($parliamentMember->activities->take(12) as $activity)<div class="parliament-activity"><span class="parliament-activity-type">{{ $activity->type }}</span><span class="parliament-activity-title">{{ $activity->title }}</span><span class="parliament-activity-meta">{{ collect([$activity->occurred_on?->format('d M Y'), $activity->decision])->filter()->implode(' • ') }}</span></div>@endforeach</div></div>@endif
                    </div>
                </section>
                @endif
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

@if($canEditCoverPhoto)
<div class="profile-cover-modal{{ $errors->has('cover_photo') ? ' is-open' : '' }}" data-public-cover-modal aria-hidden="{{ $errors->has('cover_photo') ? 'false' : 'true' }}">
    <div class="profile-cover-dialog" role="dialog" aria-modal="true" aria-labelledby="publicCoverTitle">
        <div class="profile-cover-head">
            <div>
                <h3 class="profile-cover-title" id="publicCoverTitle">{{ $candidate->cover_photo ? 'Edit Cover Photo' : 'Add Cover Photo' }}</h3>
                <p class="profile-cover-note">Upload a wide image for the top of your public campaign profile.</p>
            </div>
            <button type="button" class="profile-cover-close" data-public-cover-close aria-label="Close cover photo form"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('aspirant.cover-photo.update') }}" enctype="multipart/form-data" class="profile-cover-form">
            @csrf
            <div class="profile-cover-preview" data-public-cover-preview>
                @if($candidate->cover_photo)
                    <img src="{{ Storage::url($candidate->cover_photo) }}" alt="{{ $candidate->name }} cover photo preview">
                @else
                    <span>No cover photo selected</span>
                @endif
            </div>
            <label>
                Cover photo
                <input type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp" required data-public-cover-input>
            </label>
            @error('cover_photo')
                <p class="empty-note">{{ $message }}</p>
            @enderror
            <button type="submit" class="profile-cover-submit">Save Cover Photo</button>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-public-cover-modal]');
    const open = document.querySelector('[data-public-cover-open]');
    const close = document.querySelector('[data-public-cover-close]');
    const input = document.querySelector('[data-public-cover-input]');
    const preview = document.querySelector('[data-public-cover-preview]');

    const closeModal = () => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    open?.addEventListener('click', () => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        input?.focus();
    });
    close?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    input?.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file || !preview) return;
        preview.innerHTML = `<img src="${URL.createObjectURL(file)}" alt="Selected cover photo preview">`;
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeModal();
    });
});
</script>
@endif

@endsection
