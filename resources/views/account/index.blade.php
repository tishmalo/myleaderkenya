@extends('layouts.landing')

@section('title', 'My Account - My Leader Kenya')

@push('styles')
<style>
.account-shell{min-height:100vh;background:#09090b;color:#fff;padding:18px}.account-layout{display:grid;grid-template-columns:270px minmax(0,1fr);gap:18px;max-width:1440px;margin:0 auto}.account-sidebar{position:sticky;top:18px;display:flex;max-height:calc(100vh - 36px);min-height:calc(100vh - 36px);flex-direction:column;border:1px solid #27272a;border-radius:18px;background:#111113;padding:18px;box-shadow:0 20px 60px rgba(0,0,0,.25)}.account-sidebar-brand{display:flex;align-items:center;gap:12px;border-bottom:1px solid #27272a;padding:4px 4px 18px;color:#fff;text-decoration:none}.account-sidebar-logo{display:grid;width:43px;height:43px;place-items:center;border-radius:13px;background:rgba(16,185,129,.14);color:#34d399;font-size:21px}.account-sidebar-brand small,.account-sidebar-brand strong{display:block}.account-sidebar-brand small{color:#71717a;font-size:10px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}.account-sidebar-brand strong{margin-top:3px;font:700 21px 'Oswald',sans-serif}.account-sidebar-nav{display:grid;gap:7px;margin-top:18px}.account-sidebar-link,.account-sidebar-logout{display:flex;min-height:45px;align-items:center;gap:12px;border:1px solid transparent;border-radius:11px;padding:0 13px;color:#a1a1aa;font-size:13px;font-weight:800;text-decoration:none}.account-sidebar-link i,.account-sidebar-logout i{width:18px;text-align:center}.account-sidebar-link:hover,.account-sidebar-link.is-active{border-color:#3f3f46;background:#202023;color:#fff}.account-sidebar-link.is-active i{color:#34d399}.account-sidebar-link.is-primary{margin:8px 0;background:#059669;color:#fff}.account-sidebar-link.is-primary:hover{border-color:#10b981;background:#047857}.account-sidebar-footer{margin-top:auto;border-top:1px solid #27272a;padding-top:16px}.account-sidebar-logout{width:100%;background:rgba(220,38,38,.08);color:#fca5a5;cursor:pointer}.account-sidebar-logout:hover{border-color:rgba(239,68,68,.25);background:rgba(220,38,38,.14)}.account-content{min-width:0;border:1px solid #202024;border-radius:18px;background:#0d0d0f;padding:clamp(22px,4vw,46px)}.account-kicker{color:#34d399;font-size:11px;font-weight:900;letter-spacing:.22em;text-transform:uppercase}.account-title{margin:8px 0 0;font:700 clamp(30px,4vw,46px)/1.1 'Oswald',sans-serif}.account-subtitle{margin-top:9px;color:#a1a1aa}.account-alert{margin-top:20px;border:1px solid;padding:13px 15px;border-radius:12px}.account-alert.success{border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.1);color:#a7f3d0}.account-alert.error{border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.1);color:#fecaca}.account-section{scroll-margin-top:20px;margin-top:38px}.account-section-head{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:14px}.account-section h2{font:700 23px 'Oswald',sans-serif}.account-count{border-radius:999px;background:#202023;padding:5px 10px;color:#a1a1aa;font-size:11px;font-weight:800}.account-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.account-card,.account-empty{border:1px solid #29292d;border-radius:15px;background:#17171a;padding:18px}.account-card h3{font-size:17px;font-weight:800}.account-meta{margin-top:5px;color:#a1a1aa;font-size:13px}.account-dashboard-button{display:inline-flex;margin-top:16px;border-radius:10px;background:#b91c1c;padding:10px 14px;color:#fff;font-size:12px;font-weight:900}.account-empty{color:#71717a}.account-claims{overflow:hidden;border:1px solid #29292d;border-radius:15px;background:#17171a}.account-claim{display:flex;align-items:center;justify-content:space-between;gap:15px;border-bottom:1px solid #29292d;padding:17px}.account-claim:last-child{border-bottom:0}.account-status{border-radius:999px;padding:5px 10px;font-size:10px;font-weight:900;text-transform:uppercase}.account-status.approved{background:#052e27;color:#6ee7b7}.account-status.rejected{background:#450a0a;color:#fca5a5}.account-status.pending{background:#451a03;color:#fcd34d}@media(max-width:860px){.account-shell{padding:10px}.account-layout{grid-template-columns:1fr}.account-sidebar{position:static;min-height:auto;max-height:none}.account-sidebar-nav{grid-template-columns:repeat(2,minmax(0,1fr))}.account-sidebar-footer{margin-top:16px}.account-content{padding:24px}.account-grid{grid-template-columns:1fr}}@media(max-width:520px){.account-sidebar-nav{grid-template-columns:1fr}.account-content{padding:20px}.account-claim{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
<div class="flag-stripe"></div>
@include('components.frontend-nav')

<main class="account-shell">
    <div class="account-layout">
        @include('components.my-account-sidebar')

        <div class="account-content">
            <header id="overview">
                <p class="account-kicker">Account overview</p>
                <h1 class="account-title">Welcome, {{ auth()->user()->name }}</h1>
                <p class="account-subtitle">Manage your aspirant claims and open approved campaign dashboards.</p>
            </header>

            @if(session('success'))<div class="account-alert success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="account-alert error">{{ $errors->first() }}</div>@endif

            <section class="account-section" id="dashboards">
                <div class="account-section-head"><h2>Available dashboards</h2><span class="account-count">{{ $candidates->count() }}</span></div>
                <div class="account-grid">
                    @forelse($candidates as $candidate)
                        <article class="account-card">
                            <h3>{{ $candidate->name }}</h3>
                            <p class="account-meta">{{ $candidate->position?->name ?: 'Aspirant' }} @if($candidate->politicalParty) &middot; {{ $candidate->politicalParty->name }} @endif</p>
                            <form method="POST" action="{{ route('my-account.aspirants.select') }}">@csrf<input type="hidden" name="candidate_id" value="{{ $candidate->id }}"><button class="account-dashboard-button">Login to Dashboard</button></form>
                        </article>
                    @empty
                        <div class="account-empty">No approved aspirant dashboards yet. Approved claims will appear here.</div>
                    @endforelse
                </div>
            </section>

            <section class="account-section" id="claims">
                <div class="account-section-head"><h2>Claim requests</h2><span class="account-count">{{ $claims->count() }}</span></div>
                <div class="account-claims">
                    @forelse($claims as $claim)
                        <article class="account-claim"><div><strong>{{ $claim->candidate?->name ?? 'Aspirant' }}</strong><p class="account-meta">{{ str($claim->relationship)->headline() }}</p></div><span class="account-status {{ $claim->status }}">{{ $claim->status }}</span></article>
                    @empty
                        <div class="account-empty">You have not submitted any claims.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</main>
@endsection