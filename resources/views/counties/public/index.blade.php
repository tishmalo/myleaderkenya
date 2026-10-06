@extends('layouts.landing')

@section('title', 'Counties - My Leader Kenya')
@section('meta_description', 'Browse all Kenyan counties and meet the aspirants running in each one.')

@section('content')
<div class="county-directory">
    <div class="flag-stripe"></div>
    @include('components.frontend-nav')

    <header class="county-directory-hero">
        <p class="county-directory-kicker">Voter</p>
        <h1 class="county-directory-title">Counties</h1>
        <p class="county-directory-sub">Pick a county to see everyone running there, from Governor to MCA.</p>
    </header>

    <main class="county-directory-shell">
        <div class="county-card-grid">
            @forelse($counties as $county)
                <a href="{{ route('county.show', $county) }}" class="county-card">
                    @if(!empty($county->image))
                        <img src="{{ Storage::url($county->image) }}" alt="{{ $county->name }}">
                    @else
                        <div class="county-card-placeholder">{{ substr($county->name, 0, 1) }}</div>
                    @endif
                    <span class="county-card-label">{{ $county->name }}</span>
                    <span class="county-card-meta">{{ $county->approved_aspirant_count }} aspirant{{ $county->approved_aspirant_count !== 1 ? 's' : '' }}</span>
                </a>
            @empty
                <div class="county-directory-empty">
                    <p>No counties have been added yet.</p>
                </div>
            @endforelse
        </div>
    </main>
</div>

<style>
    .county-directory { min-height: 100vh; background: #0a0a0a; color: #f5f5f0; }
    .county-directory-hero { max-width: 1280px; margin: 0 auto; padding: 56px 32px 10px; }
    .county-directory-kicker { margin: 0; color: #34d399; font-size: 11px; font-weight: 900; letter-spacing: .2em; text-transform: uppercase; }
    .county-directory-title { margin: 8px 0 0; font-size: clamp(30px, 5vw, 46px); font-weight: 800; }
    .county-directory-sub { margin: 10px 0 0; color: #a1a1aa; font-size: 15px; }
    .county-directory-shell { max-width: 1280px; margin: 0 auto; padding: 26px 32px 80px; }
    .county-card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 22px; }
    .county-card { position: relative; min-height: 200px; border-radius: 18px; overflow: hidden; display: block; background: #141414; border: 1px solid rgba(255,255,255,0.08); text-decoration: none; box-shadow: 0 24px 60px rgba(0,0,0,0.35); }
    .county-card img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .45s ease; }
    .county-card:hover img { transform: scale(1.05); }
    .county-card::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.06), rgba(0,0,0,0.55)); }
    .county-card-placeholder { position: absolute; inset: 0; display: grid; place-items: center; font-size: 64px; font-weight: 800; color: rgba(255,255,255,.14); background: linear-gradient(135deg, rgba(187,0,0,.2), rgba(0,102,0,.2)); }
    .county-card-label, .county-card-meta { position: absolute; left: 0; right: 0; z-index: 1; padding: 0 22px; }
    .county-card-label { bottom: 44px; font-family: 'Oswald', sans-serif; font-size: 24px; font-weight: 700; color: #fff; }
    .county-card-meta { bottom: 20px; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #34d399; }
    .county-card:hover { border-color: rgba(0,168,107,.4); }
    .county-directory-empty { grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #a1a1aa; }
    @media (max-width: 640px) { .county-directory-hero, .county-directory-shell { padding-left: 16px; padding-right: 16px; } }
</style>
@endsection
