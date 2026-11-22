@extends('layouts.landing')

@section('title', 'Coordinators Guide - My Leader Kenya')
@section('meta_description', 'How My Leader Kenya coordinators work: the roles, the tools and what a good day of coordination looks like.')

@push('styles')
    @vite('resources/css/views/landing.css')
@endpush

@section('content')
<style>
body { font-family:'Barlow',sans-serif; background:#0a0a0a; color:var(--kenya-white); }
.cg-hero { padding:110px 32px 56px; background:linear-gradient(135deg, rgba(187,0,0,0.18), rgba(0,102,0,0.14)); border-bottom:1px solid rgba(255,255,255,0.07); }
.cg-inner { max-width:1120px; margin:0 auto; }
.cg-kicker { color:#00a651; text-transform:uppercase; letter-spacing:.12em; font-weight:700; font-size:13px; }
.cg-title { font-family:'Oswald',sans-serif; font-size:clamp(42px,7vw,72px); line-height:.98; margin:14px 0 20px; }
.cg-excerpt { max-width:760px; color:rgba(245,245,240,.68); font-size:19px; line-height:1.6; }
.cg-body { padding:64px 32px 90px; }
.cg-grid { max-width:1120px; margin:0 auto; display:grid; gap:40px; grid-template-columns:240px minmax(0,1fr); align-items:start; }
.cg-toc { position:sticky; top:96px; }
.cg-toc-label { font-size:10px; font-weight:700; letter-spacing:3px; text-transform:uppercase; color:rgba(245,245,240,.35); margin-bottom:14px; }
.cg-toc nav { border-left:1px solid rgba(255,255,255,0.08); padding-left:14px; }
.cg-toc a { display:block; padding:5px 0; color:rgba(245,245,240,.55); font-size:14px; text-decoration:none; transition:color .2s; }
.cg-toc a.is-sub { padding-left:14px; font-size:12.5px; }
.cg-toc a:hover { color:#00a651; }
.cg-article { background:#111; border:1px solid rgba(255,255,255,0.07); border-radius:20px; padding:36px 40px; }
.cg-article > *:first-child { margin-top:0; }
.cg-article h2 { margin:2.5rem 0 .75rem; padding-bottom:.5rem; border-bottom:1px solid rgba(255,255,255,0.08); color:#fff; font-family:'Oswald',sans-serif; font-size:1.45rem; font-weight:600; scroll-margin-top:96px; }
.cg-article h2:first-child { margin-top:0; }
.cg-article h3 { margin:1.75rem 0 .5rem; color:#fff; font-size:1.08rem; font-weight:700; scroll-margin-top:96px; }
.cg-article h4 { margin:1.25rem 0 .5rem; color:#e4e4e7; font-size:.98rem; font-weight:700; scroll-margin-top:96px; }
.cg-article p { margin:.8rem 0; color:rgba(245,245,240,.74); font-size:16.5px; line-height:1.8; }
.cg-article ul, .cg-article ol { margin:.8rem 0 .8rem 1.3rem; }
.cg-article ul { list-style:disc; }
.cg-article ol { list-style:decimal; }
.cg-article li { margin:.35rem 0; padding-left:.25rem; color:rgba(245,245,240,.74); line-height:1.75; }
.cg-article li::marker { color:#00a651; }
.cg-article strong { color:#fff; font-weight:700; }
.cg-article a { color:#34d399; text-decoration:underline; text-underline-offset:2px; }
.cg-article code { background:#18181b; border:1px solid rgba(255,255,255,0.08); border-radius:6px; padding:.1rem .35rem; color:#a7f3d0; font-size:.85em; }
.cg-article pre { margin:1rem 0; overflow-x:auto; background:#18181b; border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:1rem; }
.cg-article pre code { border:0; background:transparent; padding:0; color:#d4d4d8; }
.cg-article blockquote { margin:1rem 0; border-left:3px solid #00a651; background:#18181b; border-radius:0 12px 12px 0; padding:.75rem 1rem; color:rgba(245,245,240,.6); }
.cg-article hr { margin:2rem 0; border-color:rgba(255,255,255,0.08); }
.cg-cta { max-width:1120px; margin:0 auto; padding:0 32px 90px; }
.cg-cta-card { border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:32px; background:rgba(0,168,107,0.08); display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:18px; }
.cg-cta-title { font-family:'Oswald',sans-serif; font-size:24px; font-weight:700; }
.cg-cta-copy { color:rgba(245,245,240,.6); font-size:15px; margin-top:6px; }
.cg-cta-button { display:inline-flex; align-items:center; gap:10px; background:#bb0000; color:#fff; padding:14px 22px; border-radius:8px; text-decoration:none; font-weight:700; white-space:nowrap; }
.cg-cta-button:hover { background:#cc0000; }
@media(max-width:900px){
  .cg-grid { grid-template-columns:1fr; }
  .cg-toc { position:static; order:2; }
  .cg-hero { padding:90px 20px 44px; }
  .cg-body { padding:48px 20px 70px; }
  .cg-article { padding:24px 20px; }
  .cg-cta { padding:0 20px 70px; }
}
</style>

@include('components.frontend-nav')

<section class="cg-hero">
    <div class="cg-inner">
        <div class="cg-kicker">Team</div>
        <h1 class="cg-title">Coordinators Guide</h1>
        <p class="cg-excerpt">
            How we work: the roles, the tools and what a good day of coordination looks like at My Leader Kenya.
        </p>
    </div>
</section>

<section class="cg-body">
    <div class="cg-grid">
        @if(! empty($headings))
            <aside class="cg-toc">
                <p class="cg-toc-label">On this page</p>
                <nav>
                    @foreach($headings as $heading)
                        @continue($heading['level'] > 3)
                        <a href="#{{ $heading['anchor'] }}" class="{{ $heading['level'] === 3 ? 'is-sub' : '' }}">
                            {{ $heading['title'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>
        @endif

        <article class="cg-article">
            {!! $html !!}
        </article>
    </div>
</section>

<section class="cg-cta">
    <div class="cg-cta-card">
        <div>
            <div class="cg-cta-title">Want to coordinate in your county?</div>
            <p class="cg-cta-copy">Join the team and help voters in your area verify their details and follow the 2027 race.</p>
        </div>
        <a href="{{ route('contact.public') }}" class="cg-cta-button">Talk to us <i class="fas fa-arrow-right"></i></a>
    </div>
</section>
@endsection
