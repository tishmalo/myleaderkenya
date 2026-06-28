@extends('layouts.landing')

@section('title', $pageData['content']['meta_title'] ?: ($pageData['content']['title'] . ' - Tuko Kadi'))
@section('meta_description', $pageData['content']['meta_description'] ?: $pageData['content']['excerpt'])

@section('content')
<style>
body { font-family:'Barlow',sans-serif; background:#0a0a0a; color:var(--kenya-white); }
.fp-hero { padding:110px 32px 72px; background:linear-gradient(135deg, rgba(187,0,0,0.18), rgba(0,102,0,0.14)); border-bottom:1px solid rgba(255,255,255,0.07); }
.fp-inner { max-width:1120px; margin:0 auto; }
.fp-kicker { color:#00a651; text-transform:uppercase; letter-spacing:.12em; font-weight:700; font-size:13px; }
.fp-title { font-family:'Oswald',sans-serif; font-size:clamp(42px,7vw,78px); line-height:.95; margin:14px 0 22px; }
.fp-excerpt { max-width:760px; color:rgba(245,245,240,.68); font-size:20px; line-height:1.6; }
.fp-body { padding:72px 32px; }
.fp-content { max-width:820px; color:rgba(245,245,240,.76); font-size:18px; line-height:1.85; white-space:pre-line; }
.fp-cta { display:inline-flex; margin-top:34px; align-items:center; gap:10px; background:#bb0000; color:white; padding:14px 22px; border-radius:8px; text-decoration:none; font-weight:700; }
.fp-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:18px; margin-top:44px; }
.fp-stat { background:#141414; border:1px solid rgba(255,255,255,.07); border-radius:8px; padding:26px; }
.fp-stat-label { color:rgba(245,245,240,.52); font-size:13px; text-transform:uppercase; letter-spacing:.08em; }
.fp-stat-value { font-family:'Oswald',sans-serif; font-size:42px; margin-top:8px; }
.fp-counties { margin-top:28px; display:grid; gap:12px; max-width:820px; }
.fp-county { display:flex; justify-content:space-between; gap:18px; background:#141414; border:1px solid rgba(255,255,255,.07); border-radius:8px; padding:16px 18px; }
@media(max-width:760px){ .fp-hero{padding:88px 20px 54px}.fp-body{padding:52px 20px}.fp-stats{grid-template-columns:1fr}.fp-title{font-size:44px} }
</style>

@include('components.frontend-nav')

<section class="fp-hero">
    <div class="fp-inner">
        <div class="fp-kicker">{{ $pageData['content']['title'] }}</div>
        <h1 class="fp-title">{{ $pageData['content']['hero_title'] }}</h1>
        @if($pageData['content']['excerpt'])
            <p class="fp-excerpt">{{ $pageData['content']['excerpt'] }}</p>
        @endif
    </div>
</section>

<section class="fp-body">
    <div class="fp-inner">
        <div class="fp-content">{{ $pageData['content']['content'] }}</div>

        @if($pageData['key'] === 'live-stats')
            <div class="fp-stats">
                <div class="fp-stat">
                    <div class="fp-stat-label">Confirmed Voters</div>
                    <div class="fp-stat-value">{{ number_format($voterStats['confirmedVoters'] ?? 0) }}</div>
                </div>
                <div class="fp-stat">
                    <div class="fp-stat-label">Registered Users</div>
                    <div class="fp-stat-value">{{ number_format($totalUsers ?? 0) }}</div>
                </div>
                <div class="fp-stat">
                    <div class="fp-stat-label">Polling Stations</div>
                    <div class="fp-stat-value">{{ number_format($stationsCount ?? 0) }}</div>
                </div>
            </div>

            @if(! empty($voterStats['byCounty']) && $voterStats['byCounty']->count())
                <div class="fp-counties">
                    @foreach($voterStats['byCounty']->take(8) as $county)
                        <div class="fp-county">
                            <span>{{ $county->county }}</span>
                            <strong>{{ number_format($county->count) }}</strong>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        @if($pageData['content']['cta_label'] && $pageData['content']['cta_url'])
            @php($ctaUrl = Str::startsWith($pageData['content']['cta_url'], ['http://', 'https://', 'mailto:', 'tel:', '#']) ? $pageData['content']['cta_url'] : url($pageData['content']['cta_url']))
            <a href="{{ $ctaUrl }}" class="fp-cta">{{ $pageData['content']['cta_label'] }} <i class="fas fa-arrow-right"></i></a>
        @endif
    </div>
</section>
@endsection
