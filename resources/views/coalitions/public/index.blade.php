@extends('layouts.landing')

@section('title', 'Coalitions - My Leader Kenya')
@section('meta_description', 'Explore published political coalitions and the parties that form them.')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap');
:root { --kenya-red:#BB0000; --kenya-black:#111111; --kenya-white:#F5F5F0; --green-bright:#00A86B; --kenya-green:#006600; }
* { box-sizing:border-box; }
body { font-family:'Barlow',sans-serif; background:#0a0a0a; color:var(--kenya-white); }
h1,h2,h3 { font-family:'Oswald',sans-serif; }
.flag-stripe { height:4px; background:linear-gradient(90deg, var(--kenya-green) 33%, #111 33% 66%, var(--kenya-red) 66%); }
.party-hero { padding:78px 32px 58px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.06); background:#0a0a0a; }
.party-eyebrow { display:inline-flex; align-items:center; gap:8px; color:#ff6666; font-size:11px; font-weight:700; letter-spacing:2.4px; text-transform:uppercase; margin-bottom:18px; }
.party-eyebrow .dot { width:7px; height:7px; border-radius:50%; background:var(--green-bright); }
.party-hero h1 { font-size:clamp(42px,6vw,72px); line-height:1; margin:0 0 18px; }
.party-hero h1 em { color:var(--green-bright); font-style:normal; }
.party-hero p { max-width:720px; margin:0 auto; color:rgba(245,245,240,0.62); font-size:18px; line-height:1.7; }
.party-grid { max-width:1280px; margin:0 auto; padding:64px 32px 90px; display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:22px; }
.party-card { position:relative; display:flex; flex-direction:column; min-height:360px; background:#151515; border:1px solid rgba(255,255,255,0.07); border-radius:18px; overflow:hidden; text-decoration:none; transition:border-color .25s, transform .25s, box-shadow .25s; }
.party-card:hover { border-color:rgba(0,168,107,0.35); transform:translateY(-4px); box-shadow:0 24px 60px rgba(0,0,0,0.45); }
.party-card-img { height:190px; background:#0f0f0f; overflow:hidden; }
.party-card-img img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .4s; }
.party-card:hover .party-card-img img { transform:scale(1.04); }
.party-card-no-img { height:190px; display:flex; align-items:center; justify-content:center; color:rgba(245,245,240,0.22); font-size:48px; background:linear-gradient(135deg, color-mix(in srgb, var(--brand) 24%, transparent), rgba(0,102,0,0.12)); }
.party-card-body { padding:24px; display:flex; flex-direction:column; flex:1; }
.party-card-kicker { color:var(--green-bright); font-size:10px; font-weight:700; letter-spacing:2px; text-transform:uppercase; margin-bottom:12px; }
.party-card-title { color:white; font-family:'Oswald',sans-serif; font-size:25px; font-weight:700; line-height:1.15; margin-bottom:12px; }
.party-card-excerpt { color:rgba(245,245,240,0.55); line-height:1.65; font-size:14px; }
.party-card-footer { margin-top:auto; padding-top:22px; color:#ff6666; font-size:12px; font-weight:700; letter-spacing:1.4px; text-transform:uppercase; }
.party-empty { grid-column:1/-1; text-align:center; padding:80px 20px; color:rgba(245,245,240,0.35); }
.party-pagination { max-width:1280px; margin:-50px auto 80px; padding:0 32px; display:flex; justify-content:center; }
.party-back { max-width:960px; margin:0 auto; padding:30px 32px 0; }
.party-back a { display:inline-flex; align-items:center; gap:8px; color:rgba(245,245,240,0.38); text-decoration:none; font-size:12px; font-weight:700; letter-spacing:1.6px; text-transform:uppercase; }
.party-back a:hover { color:var(--green-bright); }
.party-show { max-width:1120px; margin:34px auto 0; padding:0 32px 96px; }
.party-show-card { overflow:hidden; border-radius:20px; border:1px solid rgba(255,255,255,0.08); background:#141414; box-shadow:0 38px 80px rgba(0,0,0,0.45); }
.party-show-head { min-height:320px; display:grid; place-items:end start; padding:42px; background:linear-gradient(135deg, rgba(187,0,0,0.18), rgba(0,102,0,0.15)); position:relative; }
.party-show-head.has-logo { grid-template-columns:220px 1fr; gap:32px; place-items:center start; }
.party-show-logo { width:220px; height:220px; border-radius:24px; overflow:hidden; background:#0f0f0f; border:1px solid rgba(255,255,255,0.08); }
.party-show-logo img { width:100%; height:100%; object-fit:cover; }
.party-show-kicker { color:var(--green-bright); font-size:11px; font-weight:800; letter-spacing:2.3px; text-transform:uppercase; margin-bottom:16px; }
.party-show-title { font-size:clamp(34px,5vw,64px); line-height:1; margin:0 0 18px; color:white; }
.party-show-excerpt { max-width:720px; color:rgba(245,245,240,0.68); line-height:1.7; font-size:18px; }
.party-content { padding:36px; color:rgba(245,245,240,0.72); font-size:17px; line-height:1.85; }
.party-content a { color:var(--green-bright); }
.member-section { margin-top:28px; padding:30px 36px 40px; border-top:1px solid rgba(255,255,255,0.07); }
.member-section h2 { margin:0 0 18px; color:white; }
.member-party-list { display:flex; flex-wrap:wrap; gap:12px; }
.member-party-chip { display:inline-flex; align-items:center; gap:10px; padding:10px 14px; border-radius:999px; background:#101010; border:1px solid color-mix(in srgb, var(--brand) 36%, rgba(255,255,255,0.1)); color:white; text-decoration:none; }
.member-party-chip img,.member-party-chip span { width:28px; height:28px; border-radius:50%; display:grid; place-items:center; background:var(--brand); color:white; object-fit:cover; font-size:11px; }
.member-party-empty { color:rgba(245,245,240,0.45); }
@media (max-width:768px) { .party-hero { padding:58px 18px 42px; } .party-grid,.party-show,.party-back { padding-left:18px; padding-right:18px; } .party-show-head,.party-content,.member-section { padding:24px; } .party-show-head.has-logo { display:block; } .party-show-logo { width:130px; height:130px; margin-bottom:20px; } }
</style>
<div class="flag-stripe"></div>
@include('components.frontend-nav')
<section class="party-hero"><div class="party-eyebrow"><span class="dot"></span> Coalition Directory</div><h1>Political <em>Coalitions</em></h1><p>Coalitions are built from political parties. Explore the published alliances and their member parties.</p></section>
<section class="party-grid">
@forelse($coalitions as $coalition)
    <x-public-party-card :item="$coalition" type="Coalition" :url="route('coalitions.show', $coalition->slug)" />
@empty
    <div class="party-empty"><h3>No coalitions published yet.</h3><p>Published coalitions will appear here automatically.</p></div>
@endforelse
</section>
@if($coalitions->hasPages())<div class="party-pagination">{{ $coalitions->links() }}</div>@endif
@endsection

