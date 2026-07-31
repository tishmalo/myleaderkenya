@extends('layouts.landing')

@section('title', ($politicalParty->meta_title ?: $politicalParty->name) . ' - Political Party')
@section('meta_description', $politicalParty->meta_description ?: ($politicalParty->excerpt ?: Str::limit(strip_tags($politicalParty->content), 155)))
@section('og_image', $politicalParty->logo ? Storage::url($politicalParty->logo) : asset('images/myleader.png'))

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
.party-aspirants { margin-top:32px; padding:36px; border-radius:20px; border:1px solid rgba(255,255,255,0.08); background:#141414; }
.party-aspirants-head { display:flex; align-items:end; justify-content:space-between; gap:18px; margin-bottom:26px; }
.party-aspirants-head h2 { margin:0 0 6px; color:white; font-size:32px; }
.party-aspirants-head p { margin:0; color:rgba(245,245,240,0.45); }
.party-aspirants-count { color:var(--green-bright); font-size:12px; font-weight:800; letter-spacing:1.3px; text-transform:uppercase; white-space:nowrap; }
.party-position-group + .party-position-group { margin-top:38px; padding-top:34px; border-top:1px solid rgba(255,255,255,.08); }
.party-position-head { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; }
.party-position-title { display:flex; align-items:center; gap:12px; margin:0; color:white; font-size:25px; }
.party-position-title::before { content:''; width:4px; height:28px; border-radius:999px; background:linear-gradient(to bottom,var(--kenya-red),var(--kenya-green)); }
.party-position-count { color:rgba(245,245,240,.45); font-size:11px; font-weight:800; letter-spacing:1.2px; text-transform:uppercase; white-space:nowrap; }
.party-aspirant-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:18px; }
.asp-card { background:#101010; border:1px solid rgba(255,255,255,0.07); border-radius:20px; overflow:hidden; position:relative; transition:border-color .3s,transform .3s,box-shadow .3s; display:flex; flex-direction:column; }
.asp-card:hover { border-color:rgba(0,168,107,.35); transform:translateY(-4px); box-shadow:0 24px 60px rgba(0,0,0,.5),0 0 0 1px rgba(0,168,107,.15); }
.asp-card-photo { position:relative; height:210px; overflow:hidden; }
.asp-card-photo img { width:100%; height:100%; object-fit:cover; object-position:top center; transition:transform .5s ease; }
.asp-card:hover .asp-card-photo img { transform:scale(1.05); }
.asp-card-photo-placeholder { width:100%; height:100%; background:linear-gradient(135deg,rgba(187,0,0,.2),rgba(0,102,0,.2)); display:flex; align-items:center; justify-content:center; }
.asp-card-photo-placeholder .initials { font-family:'Oswald',sans-serif; font-size:52px; font-weight:700; color:rgba(255,255,255,.12); line-height:1; }
.asp-card-photo-overlay { position:absolute; inset:0; background:linear-gradient(to top,#101010 0%,rgba(16,16,16,.3) 50%,transparent 100%); }
.asp-card-position-badge { position:absolute; top:14px; left:14px; background:rgba(0,0,0,.65); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,.1); border-radius:8px; padding:5px 12px; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:rgba(245,245,240,.7); }
.asp-card-county-tag { position:absolute; bottom:14px; right:14px; display:flex; align-items:center; gap:6px; background:rgba(0,0,0,.6); backdrop-filter:blur(8px); border:1px solid rgba(0,168,107,.25); border-radius:6px; padding:4px 10px; font-size:11px; color:var(--green-bright); font-weight:600; }
.asp-card-body { padding:16px; flex:1; display:flex; flex-direction:column; }
.asp-card-name { font-family:'Oswald',sans-serif; font-size:20px; font-weight:700; line-height:1.1; margin-bottom:4px; color:var(--kenya-white); }
.asp-card-nick { font-size:13px; color:rgba(0,168,107,.8); font-style:italic; margin-bottom:10px; }
.asp-card-divider { height:1px; background:linear-gradient(90deg,rgba(0,168,107,.2),rgba(187,0,0,.2),transparent); margin:10px 0 12px; }
.asp-card-action { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:10px; text-decoration:none; transition:background .2s,border-color .2s; margin-top:auto; }
.asp-card-action:hover { background:rgba(0,168,107,.08); border-color:rgba(0,168,107,.3); }
.asp-card-action-text { font-family:'Oswald',sans-serif; font-size:13px; font-weight:600; letter-spacing:1px; text-transform:uppercase; color:rgba(245,245,240,.7); }
.asp-card-action:hover .asp-card-action-text { color:var(--green-bright); }
.asp-card-action-arrow { width:28px; height:28px; background:var(--kenya-red); border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:11px; color:white; transition:background .2s,transform .2s; }
.asp-card:hover .asp-card-action-arrow { background:var(--green-bright); transform:translateX(2px); }
.party-aspirants-empty { grid-column:1/-1; padding:54px 20px; text-align:center; color:rgba(245,245,240,.4); }
.party-aspirants-empty i { display:block; margin-bottom:16px; color:rgba(0,168,107,.5); font-size:38px; }
.party-aspirants-empty h3 { margin:0 0 8px; color:rgba(245,245,240,.65); font-size:24px; }
.party-aspirants-empty p { margin:0; }
.party-aspirants-pagination { margin-top:30px; display:flex; justify-content:center; }
@media (max-width:900px) { .party-aspirant-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media (max-width:768px) { .party-hero { padding:58px 18px 42px; } .party-grid,.party-show,.party-back { padding-left:18px; padding-right:18px; } .party-show-head,.party-content,.member-section,.party-aspirants { padding:24px; } .party-show-head.has-logo { display:block; } .party-show-logo { width:130px; height:130px; margin-bottom:20px; } .party-aspirants-head { align-items:start; flex-direction:column; } }
@media (max-width:520px) { .party-position-head { align-items:flex-start; flex-direction:column; } .party-aspirant-grid { grid-template-columns:1fr; } .asp-card-photo { height:240px; } }
</style>
<div class="flag-stripe"></div>
@include('components.frontend-nav')
<div class="party-back"><a href="{{ route('parties.public') }}"><i class="fas fa-arrow-left"></i> All Political Parties</a></div>
<main class="party-show">
    <article class="party-show-card">
        <header class="party-show-head {{ $politicalParty->logo ? 'has-logo' : '' }}" style="background:linear-gradient(135deg, {{ $politicalParty->brand_color ?: '#00A86B' }}33, rgba(187,0,0,0.16));">
            @if($politicalParty->logo)<div class="party-show-logo"><img src="{{ Storage::url($politicalParty->logo) }}" alt="{{ $politicalParty->name }}"></div>@endif
            <div><div class="party-show-kicker">Political Party{{ $politicalParty->abbreviation ? ' / ' . $politicalParty->abbreviation : '' }}</div><h1 class="party-show-title">{{ $politicalParty->name }}</h1>@if($politicalParty->excerpt)<p class="party-show-excerpt">{{ $politicalParty->excerpt }}</p>@endif @if($politicalParty->website_url)<p><a href="{{ $politicalParty->website_url }}" target="_blank" rel="noopener" style="color:var(--green-bright);text-decoration:none;font-weight:700;">Official Website <i class="fas fa-arrow-up-right-from-square"></i></a></p>@endif<p><a href="{{ route('parties.access.create', $politicalParty) }}" style="display:inline-flex;margin-top:10px;padding:10px 16px;border:1px solid rgba(0,168,107,.45);border-radius:10px;color:var(--green-bright);text-decoration:none;font-weight:700;"><i class="fas fa-shield-halved" style="margin-right:8px"></i> Request Party Dashboard Access</a></p></div>
        </header>
        <div class="party-content">{!! nl2br(e($politicalParty->content)) !!}</div>
        <section class="member-section"><h2>Coalitions</h2><x-member-party-list :parties="$politicalParty->coalitions" route-name="coalitions.show" /></section>
    </article>
    <section class="party-aspirants" aria-labelledby="party-aspirants-title">
        <div class="party-aspirants-head">
            <div>
                <h2 id="party-aspirants-title">Aspirants vying under {{ $politicalParty->name }}</h2>
                <p>Explore approved aspirants representing this political party.</p>
            </div>
            @if($candidateTotal > 0)
                <div class="party-aspirants-count">
                    {{ number_format($candidateTotal) }}
                    {{ Str::plural('aspirant', $candidateTotal) }}
                </div>
            @endif
        </div>
        @forelse($candidateGroups as $group)
            <section class="party-position-group" aria-labelledby="position-{{ $group['position']->id }}">
                <div class="party-position-head">
                    <h3 class="party-position-title" id="position-{{ $group['position']->id }}">
                        {{ $group['position']->name }}
                    </h3>
                    <span class="party-position-count">
                        {{ number_format($group['candidates']->total()) }}
                        {{ Str::plural('aspirant', $group['candidates']->total()) }}
                    </span>
                </div>

                <div class="party-aspirant-grid">
                    @foreach($group['candidates'] as $candidate)
                        @include('aspirants.public._card', ['candidate' => $candidate])
                    @endforeach
                </div>

                @if($group['candidates']->hasPages())
                    <div class="party-aspirants-pagination">
                        {{ $group['candidates']->links() }}
                    </div>
                @endif
            </section>
        @empty
            <div class="party-aspirants-empty">
                <i class="fas fa-users"></i>
                <h3>No approved aspirants yet</h3>
                <p>Approved aspirants for this party will appear here.</p>
            </div>
        @endforelse

    </section>
</main>
@endsection

