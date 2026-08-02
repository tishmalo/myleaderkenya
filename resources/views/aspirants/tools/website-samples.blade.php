@extends('layouts.landing')

@section('title', 'Campaign Website Samples')

@section('content')
<style>
body { background:#090909; color:#f5f5f0; }
.samples-page { min-height:100vh; background:#090909; }
.samples-wrap { max-width:1180px; margin:0 auto; padding:42px 24px 84px; }
.samples-top { display:flex; justify-content:space-between; gap:18px; align-items:flex-start; margin-bottom:24px; }
.samples-kicker { color:#00A86B; font-size:12px; font-weight:900; letter-spacing:.16em; text-transform:uppercase; }
.samples-title { margin:8px 0 0; font-family:'Oswald',sans-serif; font-size:42px; line-height:1; }
.samples-note { margin:12px 0 0; color:rgba(245,245,240,.62); line-height:1.55; max-width:720px; }
.samples-btn { display:inline-flex; align-items:center; gap:8px; border-radius:8px; border:1px solid rgba(255,255,255,.12); padding:11px 14px; color:#f5f5f0; text-decoration:none; font-size:13px; font-weight:800; text-transform:uppercase; background:#141414; }
.samples-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
.sample-card { border:1px solid rgba(255,255,255,.08); border-radius:8px; background:#121212; overflow:hidden; }
.sample-card img { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; background:#181818; }
.sample-card-body { padding:16px; }
.sample-card h2 { margin:0 0 8px; font-family:'Oswald',sans-serif; font-size:22px; }
.sample-card p { margin:0 0 14px; color:rgba(245,245,240,.62); line-height:1.5; }
.sample-empty { border:1px solid rgba(255,255,255,.08); border-radius:8px; background:#121212; padding:28px; color:rgba(245,245,240,.62); }
@media (max-width:900px) { .samples-grid { grid-template-columns:1fr; } .samples-top { flex-direction:column; } }
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')

<main class="samples-page">
    <div class="samples-wrap">
        <div class="samples-top">
            <div>
                <div class="samples-kicker">Campaign Website</div>
                <h1 class="samples-title">Website Samples</h1>
                <p class="samples-note">Browse sample campaign websites. Each sample can include a PNG preview and a live website link.</p>
            </div>
            <a href="{{ route('aspirant.tools.show', 'campaign-website') }}" class="samples-btn"><i class="fas fa-arrow-left"></i> Request Website</a>
        </div>

        @if($samples->isEmpty())
            <div class="sample-empty">No website samples have been published yet.</div>
        @else
            <div class="samples-grid">
                @foreach($samples as $sample)
                    <article class="sample-card">
                        @if($sample->image_url)
                            <img src="{{ $sample->image_url }}" alt="{{ $sample->title }}">
                        @endif
                        <div class="sample-card-body">
                            <h2>{{ $sample->title }}</h2>
                            @if($sample->description)
                                <p>{{ $sample->description }}</p>
                            @endif
                            @if($sample->website_url)
                                <a href="{{ $sample->website_url }}" target="_blank" rel="noopener" class="samples-btn"><i class="fas fa-up-right-from-square"></i> Open Link</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</main>
@endsection
