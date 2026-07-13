@extends('layouts.landing')

@section('title', 'Aspirant Dashboard - My Leader Kenya')

@section('content')
<style>
body { background:#090909; color:#f5f5f0; }
.asp-dash { min-height:100vh; background:#090909; }
.asp-dash-wrap { max-width:1280px; margin:0 auto; padding:38px 28px 80px; }
.asp-top { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:28px; }
.asp-kicker { color:#00A86B; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.16em; }
.asp-title { margin:8px 0 0; font-family:'Oswald',sans-serif; font-size:clamp(34px,5vw,56px); line-height:1; }
.asp-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.asp-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:0 16px; border-radius:8px; border:1px solid rgba(255,255,255,.12); color:white; text-decoration:none; font-weight:800; font-size:13px; background:#151515; }
.asp-btn.primary { background:#00A86B; border-color:#00A86B; color:#06120d; }
.asp-btn.danger { color:#ffb4b4; }
.asp-grid { display:grid; grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr); gap:20px; align-items:start; }
.asp-panel { border:1px solid rgba(255,255,255,.08); background:#141414; border-radius:8px; padding:24px; }
.asp-panel h2 { margin:0 0 18px; font-family:'Oswald',sans-serif; font-size:24px; }
.asp-status { display:inline-flex; align-items:center; gap:8px; border-radius:999px; padding:8px 12px; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }
.asp-status.approved { background:rgba(0,168,107,.14); color:#4ade80; }
.asp-status.pending { background:rgba(250,204,21,.14); color:#fde047; }
.asp-status.rejected { background:rgba(239,68,68,.14); color:#fca5a5; }
.asp-profile { display:flex; gap:18px; align-items:center; }
.asp-avatar { width:92px; height:92px; border-radius:8px; overflow:hidden; background:#222; display:grid; place-items:center; color:#777; flex:0 0 auto; }
.asp-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
.asp-meta { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; margin-top:22px; }
.asp-meta div { background:#101010; border:1px solid rgba(255,255,255,.06); border-radius:8px; padding:14px; }
.asp-label { display:block; color:rgba(245,245,240,.48); font-size:11px; text-transform:uppercase; letter-spacing:.1em; margin-bottom:6px; }
.asp-value { color:white; font-weight:800; }
.asp-tools { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
.asp-tool { min-height:196px; display:flex; flex-direction:column; gap:12px; border:1px solid rgba(255,255,255,.08); background:#141414; border-radius:8px; padding:18px; text-decoration:none; color:white; transition:transform .2s,border-color .2s; }
.asp-tool:hover { transform:translateY(-3px); border-color:rgba(0,168,107,.45); }
.asp-tool-icon { width:40px; height:40px; border-radius:8px; display:grid; place-items:center; background:rgba(0,168,107,.12); color:#00A86B; font-size:18px; }
.asp-tool h3 { margin:0; font-family:'Oswald',sans-serif; font-size:20px; }
.asp-tool p { margin:0; color:rgba(245,245,240,.58); line-height:1.5; font-size:13px; }
.asp-tool-foot { margin-top:auto; display:flex; align-items:center; justify-content:space-between; gap:12px; color:#00A86B; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.08em; }
.asp-chip { color:rgba(245,245,240,.5); font-size:11px; border:1px solid rgba(255,255,255,.1); border-radius:999px; padding:4px 8px; }
.asp-empty { color:rgba(245,245,240,.55); line-height:1.6; }
.asp-alert { margin-bottom:18px; border-radius:8px; padding:14px 16px; color:#fde68a; background:rgba(245,158,11,.12); border:1px solid rgba(245,158,11,.28); }
.asp-scope { margin-top:10px; color:rgba(245,245,240,.58); font-size:13px; }
@media (max-width:980px) { .asp-grid { grid-template-columns:1fr; } .asp-tools { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media (max-width:640px) { .asp-dash-wrap { padding:28px 18px 64px; } .asp-top { align-items:flex-start; flex-direction:column; } .asp-tools,.asp-meta { grid-template-columns:1fr; } .asp-profile { align-items:flex-start; flex-direction:column; } }
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')

<main class="asp-dash">
    <div class="asp-dash-wrap">
        <div class="asp-top">
            <div>
                <div class="asp-kicker">Aspirant Workspace</div>
                <h1 class="asp-title">Campaign Dashboard</h1>
                @if(! empty($voterScope['label']))
                    <div class="asp-scope">Voter scope: {{ $voterScope['label'] }}</div>
                @endif
            </div>
            <div class="asp-actions">
                <a href="{{ route('campaign-tools.public') }}" class="asp-btn"><i class="fas fa-toolbox"></i> All Tools</a>
                @if($candidate && ($candidate->approval_status ?? 'approved') === 'approved')
                    <a href="{{ route('aspirants.show', $candidate) }}" class="asp-btn primary"><i class="fas fa-eye"></i> Public Profile</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="asp-btn danger"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
        </div>

        @if(session('warning'))
            <div class="asp-alert">{{ session('warning') }}</div>
        @endif

        <div class="asp-grid">
            <section class="asp-panel">
                <h2>Campaign Tools</h2>
                <div class="asp-tools">
                    @foreach($toolModules as $module)
                        <a href="{{ $module['url'] }}" class="asp-tool">
                            <div class="asp-tool-icon"><i class="{{ str_starts_with($module['icon'], 'fa-brands') ? $module['icon'] : 'fas ' . $module['icon'] }}"></i></div>
                            <h3>{{ $module['title'] }}</h3>
                            <p>{{ $module['summary'] }}</p>
                            <div class="asp-tool-foot">
                                <span>{{ $module['available'] ? 'Open Tool' : 'View Options' }}</span>
                                <span class="asp-chip">{{ $module['available'] ? 'Ready' : 'Setup' }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <aside class="asp-panel">
                <h2>Your Aspirant Profile</h2>
                @if($candidate)
                    @php($status = $candidate->approval_status ?? 'approved')
                    <div class="asp-profile">
                        <div class="asp-avatar">
                            @if($candidate->profile_picture)
                                <img src="{{ Storage::url($candidate->profile_picture) }}" alt="{{ $candidate->name }}">
                            @else
                                <i class="fas fa-user"></i>
                            @endif
                        </div>
                        <div>
                            <span class="asp-status {{ $status }}"><i class="fas fa-circle"></i> {{ ucfirst($status) }}</span>
                            <h3 style="margin:14px 0 4px;font-size:24px;font-weight:800;">{{ $candidate->name }}</h3>
                            <p style="margin:0;color:rgba(245,245,240,.58);">{{ $candidate->position->name ?? 'Aspirant' }}</p>
                        </div>
                    </div>

                    <div class="asp-meta">
                        <div><span class="asp-label">Political Party</span><span class="asp-value">{{ $candidate->politicalParty->name ?? 'Independent / Not set' }}</span></div>
                        <div><span class="asp-label">County</span><span class="asp-value">{{ $candidate->county ?: '-' }}</span></div>
                        <div><span class="asp-label">Constituency</span><span class="asp-value">{{ $candidate->constituency ?: '-' }}</span></div>
                        <div><span class="asp-label">Ward</span><span class="asp-value">{{ $candidate->ward ?: '-' }}</span></div>
                    </div>

                    @if($status === 'pending')
                        <p class="asp-empty" style="margin-top:18px;">Your profile is waiting for admin approval. You can prepare your campaign tools while the profile is reviewed.</p>
                    @elseif($status === 'rejected')
                        <p class="asp-empty" style="margin-top:18px;">Your profile needs admin attention before it can appear publicly.</p>
                    @endif
                @else
                    <p class="asp-empty">No aspirant profile is linked to this account yet. Register as an aspirant to unlock profile status and campaign setup tools.</p>
                    <a href="{{ route('aspirants.register') }}" class="asp-btn primary" style="margin-top:16px;"><i class="fas fa-user-plus"></i> Register Aspirant Profile</a>
                @endif
            </aside>
        </div>
    </div>
</main>
@endsection