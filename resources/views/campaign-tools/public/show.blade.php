@extends('layouts.landing')

@section('title', ($campaignTool->meta_title ?: $campaignTool->title) . ' - Campaign Tools')
@section('meta_description', $campaignTool->meta_description ?: ($campaignTool->excerpt ?: Str::limit(strip_tags($campaignTool->content), 155)))
@section('og_image', $campaignTool->featured_image ? Storage::url($campaignTool->featured_image) : asset('images/myleader.png'))

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap');
:root { --kenya-red:#BB0000; --kenya-black:#111111; --kenya-white:#F5F5F0; --green-bright:#00A86B; --kenya-green:#006600; }
* { box-sizing:border-box; }
body { font-family:'Barlow',sans-serif; background:#0a0a0a; color:var(--kenya-white); }
h1,h2,h3 { font-family:'Oswald',sans-serif; }
.flag-stripe { height:4px; background:linear-gradient(90deg, var(--kenya-green) 33%, #111 33% 66%, var(--kenya-red) 66%); }
.ct-back { max-width:960px; margin:0 auto; padding:30px 32px 0; }
.ct-back a { display:inline-flex; align-items:center; gap:8px; color:rgba(245,245,240,0.38); text-decoration:none; font-size:12px; font-weight:700; letter-spacing:1.6px; text-transform:uppercase; }
.ct-back a:hover { color:var(--green-bright); }
.ct-show-hero { max-width:1120px; margin:34px auto 0; padding:0 32px; }
.ct-hero-card { position:relative; overflow:hidden; border-radius:20px; border:1px solid rgba(255,255,255,0.08); background:#141414; box-shadow:0 38px 80px rgba(0,0,0,0.45); }
.ct-hero-img { height:min(520px,52vw); min-height:340px; }
.ct-hero-img img { width:100%; height:100%; object-fit:cover; display:block; }
.ct-hero-no-img { height:360px; background:linear-gradient(135deg, rgba(187,0,0,0.2), rgba(0,102,0,0.16)); }
.ct-hero-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(10,10,10,0.92), rgba(10,10,10,0.34), transparent); }
.ct-hero-content { position:absolute; left:0; right:0; bottom:0; padding:42px; }
.ct-kicker { display:inline-flex; align-items:center; gap:8px; color:var(--green-bright); font-size:11px; font-weight:800; letter-spacing:2.3px; text-transform:uppercase; margin-bottom:16px; }
.ct-kicker::before { content:''; width:28px; height:2px; background:var(--green-bright); }
.ct-title { font-size:clamp(34px,5vw,64px); line-height:1; margin:0 0 18px; color:white; }
.ct-excerpt { max-width:720px; color:rgba(245,245,240,0.68); line-height:1.7; font-size:18px; }
.ct-content-wrap { max-width:900px; margin:0 auto; padding:56px 32px 96px; }
.ct-content { background:#141414; border:1px solid rgba(255,255,255,0.07); border-radius:18px; padding:36px; color:rgba(245,245,240,0.72); font-size:17px; line-height:1.85; }
.ct-content p { margin:0 0 1.2em; }
.ct-content h2,.ct-content h3 { color:white; margin:1.6em 0 .55em; line-height:1.1; }
.ct-content ul,.ct-content ol { padding-left:1.4em; margin:0 0 1.2em; }
.ct-content a { color:var(--green-bright); }
@media (max-width:768px) { .ct-back,.ct-show-hero,.ct-content-wrap { padding-left:18px; padding-right:18px; } .ct-hero-content { padding:26px; } .ct-content { padding:24px; } }
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')

<div class="ct-back">
    <a href="{{ route('campaign-tools.public') }}"><i class="fas fa-arrow-left"></i> All Campaign Tools</a>
</div>

<section class="ct-show-hero">
    <div class="ct-hero-card">
        @if($campaignTool->featured_image)
            <div class="ct-hero-img"><img src="{{ Storage::url($campaignTool->featured_image) }}" alt="{{ $campaignTool->title }}"></div>
        @else
            <div class="ct-hero-no-img"></div>
        @endif
        <div class="ct-hero-overlay"></div>
        <div class="ct-hero-content">
            <div class="ct-kicker">Campaign Tool</div>
            <h1 class="ct-title">{{ $campaignTool->title }}</h1>
            @if($campaignTool->excerpt)<p class="ct-excerpt">{{ $campaignTool->excerpt }}</p>@endif
        </div>
    </div>
</section>

<main class="ct-content-wrap">
    <article class="ct-content">
        {!! nl2br(e($campaignTool->content)) !!}
    </article>
</main>
@endsection