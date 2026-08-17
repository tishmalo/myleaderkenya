@extends('layouts.aspirant')

@section('page_title', 'Aspirant Dashboard')

@section('content')
<style>
body { background:#080808; color:#f5f5f0; }
.asp-dash { min-height:100vh; background:#080808; }
.asp-layout { max-width:1440px; margin:0 auto; padding:26px 32px 72px; display:grid; grid-template-columns:280px minmax(0,1fr); gap:22px; align-items:start; }
.asp-sidebar { position:sticky; top:18px; max-height:calc(100vh - 36px); overflow:auto; border:1px solid rgba(255,255,255,.09); border-radius:8px; background:#101010; padding:18px; display:flex; flex-direction:column; }
.asp-sidebar-brand { border-bottom:1px solid rgba(255,255,255,.08); padding-bottom:16px; margin-bottom:14px; }
.asp-sidebar-brand span { display:block; color:#00A86B; font-size:11px; font-weight:900; letter-spacing:.16em; text-transform:uppercase; }
.asp-sidebar-brand strong { display:block; margin-top:4px; color:#fff; font-family:'Oswald',sans-serif; font-size:25px; line-height:1; }
.asp-sidebar-nav { display:grid; gap:7px; flex:1; }
.asp-sidebar-link { display:flex; align-items:center; gap:11px; min-height:42px; padding:0 12px; border:1px solid transparent; border-radius:8px; color:rgba(245,245,240,.66); text-decoration:none; font-weight:800; font-size:13px; }
.asp-sidebar-link i { width:18px; color:#00A86B; text-align:center; }
.asp-sidebar-link:hover,.asp-sidebar-link.active { color:#fff; background:#171717; border-color:rgba(0,168,107,.26); }
.asp-sidebar-footer { margin-top:18px; padding-top:14px; border-top:1px solid rgba(255,255,255,.08); }
.asp-sidebar-logout { width:100%; display:flex; align-items:center; gap:11px; min-height:42px; padding:0 12px; border:1px solid rgba(239,68,68,.22); border-radius:8px; background:rgba(239,68,68,.08); color:#ffb4b4; font:inherit; font-size:13px; font-weight:900; cursor:pointer; }
.asp-sidebar-logout i { width:18px; text-align:center; }
.asp-content { min-width:0; }
.asp-top { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; margin-bottom:18px; }
.asp-identity { display:flex; align-items:center; gap:16px; min-width:0; }
.asp-avatar { width:74px; height:74px; border-radius:8px; overflow:hidden; background:#242424; display:grid; place-items:center; color:#8b8b8b; flex:0 0 auto; font-size:22px; }
.asp-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
.asp-kicker { margin:0 0 8px; color:rgba(245,245,240,.55); font-size:12px; font-weight:900; letter-spacing:.05em; }
.asp-title { margin:0; font-family:'Oswald',sans-serif; font-size:34px; line-height:1.05; }
.asp-role { margin:8px 0 0; color:rgba(245,245,240,.65); font-size:15px; }
.asp-status { display:inline-flex; align-items:center; gap:8px; min-height:28px; border-radius:999px; padding:0 12px; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.06em; }
.asp-status i { font-size:8px; }
.asp-status.approved { background:rgba(0,168,107,.14); color:#4ade80; }
.asp-status.pending { background:rgba(250,204,21,.14); color:#fde047; }
.asp-status.rejected { background:rgba(239,68,68,.14); color:#fca5a5; }
.asp-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
.asp-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:40px; padding:0 14px; border-radius:8px; border:1px solid rgba(255,255,255,.12); color:white; text-decoration:none; font-weight:800; font-size:13px; background:#151515; white-space:nowrap; }
.asp-btn.primary { background:#d60000; border-color:#d60000; }
.asp-btn.ghost { color:rgba(245,245,240,.72); }
.asp-btn.danger { color:#ffb4b4; }
.asp-alert { margin-bottom:16px; border-radius:8px; padding:13px 15px; color:#fde68a; background:rgba(245,158,11,.12); border:1px solid rgba(245,158,11,.28); }
.asp-alert.success { color:#bbf7d0; background:rgba(34,197,94,.12); border-color:rgba(34,197,94,.28); }
.asp-section { border:1px solid rgba(255,255,255,.09); background:#101010; border-radius:8px; padding:22px; }
.asp-section[hidden] { display:none; }
.asp-section + .asp-section { margin-top:16px; }
.asp-panel-head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; margin-bottom:18px; }
.asp-panel-head h2 { margin:0; font-family:'Oswald',sans-serif; font-size:25px; line-height:1.1; display:flex; align-items:center; gap:10px; }
.asp-panel-head h2 i { color:#00A86B; font-size:20px; }
.asp-panel-note { margin:7px 0 0; color:rgba(245,245,240,.54); font-size:13px; line-height:1.45; }
.asp-badge { display:inline-flex; align-items:center; gap:7px; min-height:25px; padding:0 10px; border-radius:999px; background:rgba(0,168,107,.13); color:#4ade80; font-size:11px; font-weight:900; text-transform:uppercase; white-space:nowrap; }
.asp-badge.warn { background:rgba(245,158,11,.13); color:#fbbf24; }
.asp-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
.asp-kpi { display:flex; align-items:center; gap:14px; min-height:92px; border:1px solid rgba(255,255,255,.09); background:#111; border-radius:8px; padding:16px; }
.asp-kpi-icon { width:46px; height:46px; border-radius:8px; display:grid; place-items:center; background:rgba(0,168,107,.12); color:#00A86B; font-size:18px; flex:0 0 auto; }
.asp-kpi span { display:block; color:rgba(245,245,240,.58); font-size:12px; }
.asp-kpi strong { display:block; margin-top:2px; color:white; font-size:26px; line-height:1; font-weight:900; }
.asp-tool-groups { display:grid; gap:20px; }
.asp-tool-group-title { margin:0 0 10px; color:#00A86B; font-size:13px; font-weight:900; }
.asp-tool-group-title.warn { color:#fbbf24; }
.asp-tool-list { border:1px solid rgba(255,255,255,.08); border-radius:8px; overflow:hidden; background:#111; }
.asp-tool-row { display:grid; grid-template-columns:54px minmax(160px,.9fr) minmax(220px,1.4fr) auto; align-items:center; gap:16px; min-height:76px; padding:13px 16px; border-top:1px solid rgba(255,255,255,.07); color:white; text-decoration:none; }
.asp-tool-row:first-child { border-top:0; }
.asp-tool-row:hover { background:#151515; }
.asp-tool-row.disabled { opacity:.72; }
.asp-tool-icon { width:44px; height:44px; border-radius:8px; display:grid; place-items:center; background:rgba(0,168,107,.13); color:#00A86B; font-size:18px; }
.asp-tool-title { font-weight:900; font-size:16px; }
.asp-tool-summary { margin:0; color:rgba(245,245,240,.58); line-height:1.35; font-size:13px; }
.asp-tool-action { display:inline-flex; align-items:center; gap:8px; justify-content:center; min-width:94px; min-height:34px; padding:0 11px; border-radius:8px; border:1px solid rgba(255,255,255,.12); color:#4ade80; font-weight:900; font-size:12px; }
.asp-tool-action.warn { color:#fbbf24; }
.asp-tool-request { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:34px; padding:0 11px; border-radius:8px; border:1px solid rgba(251,191,36,.32); color:#fbbf24; background:rgba(251,191,36,.08); font-weight:900; font-size:12px; cursor:pointer; white-space:nowrap; }
.asp-tool-request:hover { background:rgba(251,191,36,.16); color:#fde68a; }
.asp-modal { position:fixed; inset:0; z-index:10030; display:none; align-items:center; justify-content:center; padding:20px; background:rgba(0,0,0,.76); backdrop-filter:blur(8px); }
.asp-modal.is-open { display:flex; }
.asp-modal-dialog { width:min(560px,100%); border:1px solid rgba(255,255,255,.12); border-radius:8px; background:#111; padding:22px; box-shadow:0 24px 70px rgba(0,0,0,.58); }
.asp-modal-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:16px; }
.asp-modal-title { margin:0; font-family:'Oswald',sans-serif; font-size:26px; line-height:1.1; }
.asp-modal-note { margin:6px 0 0; color:rgba(245,245,240,.58); font-size:13px; line-height:1.45; }
.asp-modal-close { width:36px; height:36px; border-radius:8px; border:1px solid rgba(255,255,255,.12); background:#151515; color:#fff; cursor:pointer; }
.asp-modal-form { display:grid; gap:14px; }
.asp-modal-form label { display:grid; gap:7px; color:rgba(245,245,240,.68); font-size:12px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
.asp-modal-form textarea { width:100%; min-height:120px; border:1px solid rgba(255,255,255,.12); border-radius:8px; background:#0b0b0b; color:#fff; padding:12px 13px; font:inherit; resize:vertical; }
.asp-modal-form input[type=file] { width:100%; border:1px dashed rgba(255,255,255,.18); border-radius:8px; background:#0b0b0b; color:rgba(245,245,240,.72); padding:13px; font:inherit; }
.asp-modal-submit { min-height:44px; border:0; border-radius:8px; background:#006600; color:#fff; font-weight:900; cursor:pointer; }
.asp-profile-grid { display:grid; grid-template-columns:260px minmax(0,1fr); gap:18px; align-items:start; }
.asp-profile-card { border:1px solid rgba(255,255,255,.08); border-radius:8px; background:#111; padding:18px; }
.asp-profile-card .asp-avatar { width:96px; height:96px; margin-bottom:14px; }
.asp-cover-card { margin:-18px -18px 16px; overflow:hidden; border-radius:8px 8px 0 0; border-bottom:1px solid rgba(255,255,255,.08); background:#181818; }
.asp-cover-preview { position:relative; aspect-ratio:16/7; background:linear-gradient(135deg, rgba(0,168,107,.2), rgba(187,0,0,.22)), #171717; display:grid; place-items:center; color:rgba(245,245,240,.5); }
.asp-cover-preview img { width:100%; height:100%; object-fit:cover; display:block; }
.asp-cover-empty { display:grid; gap:8px; place-items:center; font-size:12px; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
.asp-cover-empty i { font-size:20px; color:rgba(245,245,240,.35); }
.asp-cover-edit { position:absolute; right:10px; bottom:10px; display:inline-flex; align-items:center; gap:7px; min-height:32px; padding:0 10px; border:1px solid rgba(255,255,255,.14); border-radius:8px; background:rgba(0,0,0,.72); color:white; font-size:11px; font-weight:900; cursor:pointer; text-transform:uppercase; letter-spacing:.04em; }
.asp-cover-edit:hover { border-color:rgba(0,168,107,.5); color:#4ade80; }
.asp-cover-modal-preview { aspect-ratio:16/7; overflow:hidden; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:#171717; display:grid; place-items:center; color:rgba(245,245,240,.48); }
.asp-cover-modal-preview img { width:100%; height:100%; object-fit:cover; display:block; }
.asp-name { margin:0 0 5px; font-size:25px; line-height:1.08; font-weight:900; }
.asp-meta-list { display:grid; gap:0; border:1px solid rgba(255,255,255,.08); border-radius:8px; overflow:hidden; }
.asp-meta-row { display:grid; grid-template-columns:170px 1fr; gap:14px; padding:13px 14px; border-top:1px solid rgba(255,255,255,.07); }
.asp-meta-row:first-child { border-top:0; }
.asp-label { color:rgba(245,245,240,.48); font-size:11px; text-transform:uppercase; letter-spacing:.08em; }
.asp-value { color:rgba(245,245,240,.88); font-weight:800; line-height:1.35; overflow-wrap:anywhere; }
.asp-profile-note,.asp-empty { color:rgba(245,245,240,.6); line-height:1.55; margin:0; }
.asp-social-form { margin-top:18px; border:1px solid rgba(255,255,255,.08); border-radius:8px; background:#111; padding:18px; }
.asp-social-form h3 { margin:0 0 14px; color:white; font-size:17px; font-weight:900; display:flex; align-items:center; gap:9px; }
.asp-social-form h3 i { color:#00A86B; }
.asp-social-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
.asp-social-field { display:grid; gap:7px; }
.asp-social-field label { display:flex; align-items:center; gap:8px; color:rgba(245,245,240,.58); font-size:12px; font-weight:900; }
.asp-social-field label i { color:#00A86B; width:17px; text-align:center; }
.asp-social-field input { width:100%; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:#0b0b0b; color:white; padding:11px 12px; font:inherit; }
.asp-social-field input::placeholder { color:rgba(245,245,240,.28); }
.asp-media-preview { margin-bottom:10px; overflow:hidden; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:#0b0b0b; }
.asp-media-preview img { display:block; width:100%; max-height:240px; object-fit:contain; }
.asp-media-preview audio { display:block; width:100%; }
.asp-social-field input[type=file] { color:rgba(245,245,240,.68); }
.asp-activity { display:grid; gap:0; border:1px solid rgba(255,255,255,.08); border-radius:8px; overflow:hidden; }
.asp-activity-row { display:grid; grid-template-columns:42px 1fr auto; gap:12px; align-items:center; padding:13px; border-top:1px solid rgba(255,255,255,.07); }
.asp-activity-row:first-child { border-top:0; }
.asp-activity-icon { width:34px; height:34px; border-radius:8px; display:grid; place-items:center; background:rgba(0,168,107,.13); color:#00A86B; font-size:14px; }
.asp-activity-title { color:white; font-weight:900; font-size:13px; }
.asp-activity-meta { margin-top:2px; color:rgba(245,245,240,.52); font-size:12px; }
.asp-activity-time { color:rgba(245,245,240,.58); font-size:12px; white-space:nowrap; }
.asp-team-list { display:grid; gap:0; border:1px solid rgba(255,255,255,.08); border-radius:8px; overflow:hidden; background:#111; }
.asp-team-row { display:grid; grid-template-columns:minmax(180px,1.2fr) 150px 120px auto; gap:14px; align-items:center; padding:14px 16px; border-top:1px solid rgba(255,255,255,.07); }
.asp-team-row:first-child { border-top:0; }
.asp-team-name { color:white; font-weight:900; }
.asp-team-email { margin-top:3px; color:rgba(245,245,240,.48); font-size:12px; overflow-wrap:anywhere; }
.asp-team-role { color:rgba(245,245,240,.74); font-weight:800; }
.asp-team-access { display:inline-flex; justify-content:center; width:max-content; min-height:26px; padding:0 10px; align-items:center; border-radius:999px; background:rgba(34,197,94,.12); color:#86efac; font-size:11px; font-weight:900; }
.asp-team-access.off { background:rgba(245,158,11,.12); color:#fbbf24; }
.asp-team-remove { min-height:36px; border:1px solid rgba(239,68,68,.32); border-radius:8px; background:rgba(239,68,68,.08); color:#ffb4b4; padding:0 12px; font:inherit; font-size:12px; font-weight:900; cursor:pointer; }
.asp-team-remove:hover { background:rgba(239,68,68,.16); }
.asp-bars { display:grid; gap:12px; }
.asp-poll-question { margin:0 0 16px; color:rgba(245,245,240,.86); font-weight:800; line-height:1.45; }
.asp-bar-row { display:grid; grid-template-columns:minmax(120px,1fr) 1.7fr 62px; align-items:center; gap:10px; color:rgba(245,245,240,.74); font-size:12px; }
.asp-bar-row span { overflow-wrap:anywhere; }
.asp-bar-row strong { text-align:right; color:white; font-size:12px; }
.asp-bar-track { height:8px; border-radius:999px; background:rgba(255,255,255,.08); overflow:hidden; }
.asp-bar-fill { height:100%; border-radius:999px; background:#00A86B; }
.asp-bars .asp-bar-row:nth-child(2) .asp-bar-fill { background:#ef4444; }
.asp-bars .asp-bar-row:nth-child(3) .asp-bar-fill { background:#f59e0b; }
.asp-bars .asp-bar-row:nth-child(4) .asp-bar-fill { background:#3b82f6; }
.asp-priority-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
.asp-priority-card { border:1px solid rgba(255,255,255,.09); border-radius:8px; background:#111; padding:17px; transition:.2s ease; }
.asp-priority-card.is-selected { border-color:rgba(0,168,107,.5); background:rgba(0,168,107,.06); }
.asp-priority-choice { display:flex; align-items:flex-start; gap:12px; cursor:pointer; }
.asp-priority-choice input { margin-top:13px; accent-color:#00A86B; }
.asp-priority-choice-icon { width:44px; height:44px; border-radius:8px; display:grid; place-items:center; flex:0 0 auto; background:rgba(0,168,107,.13); color:#00A86B; }
.asp-priority-copy strong { display:block; color:#fff; font-size:15px; }
.asp-priority-copy > span { display:block; margin-top:4px; color:rgba(245,245,240,.48); font-size:12px; line-height:1.4; }
.asp-priority-editor { margin-top:14px; }
.asp-priority-editor[hidden] { display:none; }
.asp-priority-editor textarea { width:100%; min-height:145px; border:1px solid rgba(255,255,255,.11); border-radius:8px; background:#090909; color:#fff; padding:12px 13px; font:inherit; resize:vertical; line-height:1.5; }
.asp-priority-meta { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:8px; color:rgba(245,245,240,.42); font-size:11px; }
.asp-priority-state { border-radius:999px; padding:4px 8px; font-weight:900; text-transform:uppercase; letter-spacing:.05em; }
.asp-priority-state.approved { background:rgba(34,197,94,.12); color:#86efac; }
.asp-priority-state.pending { background:rgba(245,158,11,.12); color:#fbbf24; }
.asp-priority-state.rejected { background:rgba(239,68,68,.12); color:#fca5a5; }
.asp-priority-submit { margin-top:16px; min-height:44px; border:0; border-radius:8px; padding:0 20px; background:#006600; color:white; font-weight:900; cursor:pointer; }@media (max-width:1100px) { .asp-layout { grid-template-columns:1fr; } .asp-sidebar { position:static; max-height:none; } .asp-sidebar-nav { display:flex; overflow-x:auto; padding-bottom:4px; } .asp-sidebar-link { flex:0 0 auto; } .asp-sidebar-footer { margin-top:12px; } .asp-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); } .asp-profile-grid { grid-template-columns:1fr; } }
@media (max-width:760px) { .asp-priority-grid { grid-template-columns:1fr; } .asp-layout { padding:22px 16px 64px; } .asp-top { flex-direction:column; } .asp-actions { justify-content:flex-start; } .asp-kpis { grid-template-columns:1fr; } .asp-tool-row { grid-template-columns:44px 1fr; } .asp-tool-summary { grid-column:2; } .asp-tool-action,.asp-tool-request { grid-column:2; justify-self:start; } .asp-meta-row { grid-template-columns:1fr; gap:5px; } .asp-activity-row { grid-template-columns:36px 1fr; } .asp-activity-time { grid-column:2; } .asp-team-row { grid-template-columns:1fr; } .asp-social-grid { grid-template-columns:1fr; } }
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')

@php
    $status = $candidate?->approval_status ?? 'approved';
    $scopeLabel = $voterScope['label'] ?? 'Kenya';
    $scopeMissing = (bool) ($voterScope['missing'] ?? false);
    $readyModules = collect($toolModules)->where('available', true)->values();
    $setupModules = collect($toolModules)->where('available', false)->values();
    $sidebarItems = collect(config('aspirant-sidebar.items', []));
    $sectionIcon = function (string $section) use ($sidebarItems): string {
        return $sidebarItems->firstWhere('section', $section)['icon'] ?? 'fas fa-circle';
    };
@endphp

<main class="asp-dash">
    <div class="asp-layout">
        @include('components.aspirant-sidebar')

        <div class="asp-content">
            <div class="asp-top">
                <div class="asp-identity">
                    <div class="asp-avatar">
                        @if($candidate?->profile_picture)
                            <img src="{{ Storage::url($candidate->profile_picture) }}" alt="{{ $candidate->name }}">
                        @else
                            <i class="fas fa-user"></i>
                        @endif
                    </div>
                    <div>
                        <p class="asp-kicker">Aspirant Dashboard</p>
                        <h1 class="asp-title">{{ $candidate?->name ?? $user->name }}</h1>
                        <p class="asp-role">{{ $candidate?->position?->name ?? 'Campaign workspace' }} / {{ $scopeLabel }}</p>
                    </div>
                </div>
                <div class="asp-actions">
                    @if($candidate)
                        <span class="asp-status {{ $status }}"><i class="fas fa-circle"></i> {{ ucfirst($status) }}</span>
                    @endif
                    @if($candidate && $status === 'approved')
                        <a href="{{ route('aspirants.show', $candidate) }}" target="_blank" rel="noopener noreferrer" class="asp-btn primary"><i class="fas fa-eye"></i> Public Profile</a>
                    @endif
                    <a href="{{ route('aspirant.tokens.index') }}" class="asp-btn primary"><i class="fas fa-coins"></i> Buy Tokens</a>
                    @if($isPrimaryAspirant ?? false)
                        <a href="#team" class="asp-btn ghost" data-dashboard-section-link="team"><i class="fas fa-user-shield"></i> Team</a>
                    @endif
                    <a href="{{ route('campaign-tools.public') }}" class="asp-btn ghost"><i class="fas fa-toolbox"></i> All Tools</a>
                </div>
            </div>

            @if(session('success'))


                <div class="asp-alert success">{{ session('success') }}</div>


            @endif

            @if(session('impersonator_admin_id'))
                <div class="asp-alert">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;">
                        <span>You are viewing this dashboard as {{ $user->name }}.</span>
                        <form method="POST" action="{{ route('impersonation.stop') }}">
                            @csrf
                            <button type="submit" class="asp-btn ghost"><i class="fas fa-arrow-left"></i> Return to Admin</button>
                        </form>
                    </div>
                </div>
            @endif



            @if(session('warning'))


                <div class="asp-alert">{{ session('warning') }}</div>


            @endif
            @if($errors->any())
                <div class="asp-alert"><strong>Please review the highlighted submission.</strong><ul style="margin:8px 0 0;padding-left:20px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            @if($scopeMissing)
                <div class="asp-alert">{{ $voterScope['message'] ?? 'Ask an admin to complete your campaign jurisdiction before using voter-facing tools.' }}</div>
            @endif

            <section id="analytics" class="asp-section" data-dashboard-section="analytics">
                <div class="asp-panel-head">
                    <div>
                        <h2><i class="{{ $sectionIcon('analytics') }}"></i> Analytics</h2>
                        <p class="asp-panel-note">Core campaign numbers for {{ $scopeLabel }}.</p>
                    </div>
                    <span class="asp-badge"><i class="fas fa-circle"></i> Live</span>
                </div>
                <div class="asp-kpis" aria-label="Campaign summary">
                    <div class="asp-kpi"><div class="asp-kpi-icon"><i class="fas fa-users"></i></div><div><span>Voters</span><strong>{{ number_format($dashboardStats['scoped_voters'] ?? 0) }}</strong></div></div>
                    <div class="asp-kpi"><div class="asp-kpi-icon"><i class="fas fa-phone"></i></div><div><span>Reachable phones</span><strong>{{ number_format($dashboardStats['reachable_voters'] ?? 0) }}</strong></div></div>
                    <div class="asp-kpi"><div class="asp-kpi-icon"><i class="fas fa-square-poll-vertical"></i></div><div><span>Active polls</span><strong>{{ number_format($dashboardStats['active_polls'] ?? 0) }}</strong></div></div>
                    <div class="asp-kpi"><div class="asp-kpi-icon"><i class="fas fa-coins"></i></div><div><span>Token balance</span><strong>{{ number_format($tokenWallet?->balance ?? 0) }}</strong></div></div>
                </div>
            </section>

            <section id="campaign-tools" class="asp-section" data-dashboard-section="campaign-tools" hidden>
                <div class="asp-panel-head">
                    <div>
                        <h2><i class="{{ $sectionIcon('campaign-tools') }}"></i> Campaign Tools</h2>
                        <p class="asp-panel-note">Ready tools are available now. Setup items stay visible without taking over the page.</p>
                    </div>
                    <span class="asp-badge"><i class="fas fa-circle"></i> {{ $dashboardStats['enabled_tools'] ?? 0 }} Ready</span>
                </div>

                <div class="asp-tool-groups">
                    <div>
                        <p class="asp-tool-group-title">Ready</p>
                        @if($readyModules->isNotEmpty())
                            <div class="asp-tool-list">
                                @foreach($readyModules as $module)
                                    <a href="{{ $module['url'] }}" class="asp-tool-row" title="{{ $module['disabled_reason'] ?? '' }}">
                                        <div class="asp-tool-icon"><i class="{{ str_starts_with($module['icon'], 'fa-brands') ? $module['icon'] : 'fas ' . $module['icon'] }}"></i></div>
                                        <div class="asp-tool-title">{{ $module['title'] }}</div>
                                        <p class="asp-tool-summary">{{ $module['summary'] }}</p>
                                        <span class="asp-tool-action">Open <i class="fas fa-chevron-right"></i></span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="asp-empty">No tools are ready yet. Setup items are listed below.</p>
                        @endif
                    </div>

                    @if($setupModules->isNotEmpty())
                        <div>
                            <p class="asp-tool-group-title warn">Setup needed</p>
                            <div class="asp-tool-list">
                                @foreach($setupModules as $module)
                                    <div class="asp-tool-row disabled" title="{{ $module['disabled_reason'] ?? '' }}" aria-disabled="true">
                                        <div class="asp-tool-icon"><i class="{{ str_starts_with($module['icon'], 'fa-brands') ? $module['icon'] : 'fas ' . $module['icon'] }}"></i></div>
                                        <div class="asp-tool-title">{{ $module['title'] }}</div>
                                        <p class="asp-tool-summary">{{ $module['disabled_reason'] ?: $module['summary'] }}</p>
                                        @if($candidate)
                                            <button type="button" class="asp-tool-request" data-tool-request-open data-tool-key="{{ $module['key'] }}" data-tool-title="{{ $module['title'] }}" data-tool-id="{{ $module['tool']?->id }}" data-disabled-reason="{{ $module['disabled_reason'] ?? '' }}">
                                                Request Activation <i class="fas fa-paper-plane"></i>
                                            </button>
                                        @else
                                            <span class="asp-tool-action warn">Setup <i class="fas fa-chevron-right"></i></span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <section id="campaign-priorities" class="asp-section" data-dashboard-section="campaign-priorities" hidden>
                <div class="asp-panel-head">
                    <div><h2><i class="fas fa-bullseye"></i> Campaign Priorities</h2><p class="asp-panel-note">Choose the administrator-defined groups that matter to your campaign and explain your manifesto commitment. Every change is reviewed before it appears publicly.</p></div>
                    <span class="asp-badge warn">Admin reviewed</span>
                </div>
                @if(!$candidate)
                    <p class="asp-empty">A linked aspirant profile is required before priorities can be submitted.</p>
                @elseif($campaignPriorityCategories->isEmpty())
                    <p class="asp-empty">No campaign priority groups are currently available.</p>
                @else
                <form method="POST" action="{{ route('aspirant.campaign-priorities.update') }}" data-priority-form>@csrf @method('PUT')
                    <div class="asp-priority-grid">
                        @foreach($campaignPriorityCategories as $category)
                            @php($entry = $campaignPriorityEntries->get($category->id))
                            @php($selected = $entry || old('priorities.'.$category->id.'.manifesto') !== null)
                            <article class="asp-priority-card {{ $selected ? 'is-selected' : '' }}" data-priority-card>
                                <label class="asp-priority-choice">
                                    <input type="checkbox" value="1" {{ $selected ? 'checked' : '' }} data-priority-toggle>
                                    <span class="asp-priority-choice-icon"><i class="{{ $category->icon }}"></i></span>
                                    <span class="asp-priority-copy"><strong>{{ $category->name }}</strong><span>{{ $category->description ?: 'Add this group to your campaign manifesto.' }}</span></span>
                                </label>
                                <div class="asp-priority-editor" data-priority-editor {{ $selected ? '' : 'hidden' }}>
                                    <textarea name="priorities[{{ $category->id }}][manifesto]" maxlength="5000" placeholder="Describe your commitment, intended action and expected impact..." {{ $selected ? '' : 'disabled' }}>{{ old('priorities.'.$category->id.'.manifesto', $entry?->manifesto) }}</textarea>
                                    <div class="asp-priority-meta"><span>Maximum 5,000 characters</span>@if($entry)<span class="asp-priority-state {{ $entry->status }}">{{ $entry->status }}</span>@endif</div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <button class="asp-priority-submit"><i class="fas fa-paper-plane"></i> Save and submit for review</button>
                </form>
                @endif
            </section>
            <section id="profile" class="asp-section" data-dashboard-section="profile" hidden>
                <div class="asp-panel-head">
                    <div>
                        <h2><i class="{{ $sectionIcon('profile') }}"></i> Profile</h2>
                        <p class="asp-panel-note">Candidate identity and campaign jurisdiction.</p>
                    </div>
                    @if($candidate)
                        <span class="asp-status {{ $status }}"><i class="fas fa-circle"></i> {{ ucfirst($status) }}</span>
                    @endif
                </div>

                @if($candidate)
                    <div class="asp-profile-grid">
                        <div class="asp-profile-card">
                            <div class="asp-cover-card">
                                <div class="asp-cover-preview">
                                    @if($candidate->cover_photo)
                                        <img src="{{ Storage::url($candidate->cover_photo) }}" alt="{{ $candidate->name }} cover photo">
                                    @else
                                        <div class="asp-cover-empty">
                                            <i class="fas fa-image"></i>
                                            <span>Add cover photo</span>
                                        </div>
                                    @endif
                                    <button type="button" class="asp-cover-edit" data-cover-photo-open>
                                        <i class="fas fa-camera"></i> {{ $candidate->cover_photo ? 'Edit Cover' : 'Add Cover' }}
                                    </button>
                                </div>
                            </div>
                            <div class="asp-avatar">
                                @if($candidate->profile_picture)
                                    <img src="{{ Storage::url($candidate->profile_picture) }}" alt="{{ $candidate->name }}">
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            </div>
                            <h3 class="asp-name">{{ $candidate->name }}</h3>
                            <p class="asp-role">{{ $candidate->position->name ?? 'Aspirant' }}</p>
                        </div>

                        <div>
                            <div class="asp-meta-list">
                                <div class="asp-meta-row"><span class="asp-label">Political Party</span><span class="asp-value">{{ $candidate->politicalParty->name ?? 'Independent / Not set' }}</span></div>
                                <div class="asp-meta-row"><span class="asp-label">County</span><span class="asp-value">{{ $candidate->county ?: '-' }}</span></div>
                                <div class="asp-meta-row"><span class="asp-label">Constituency</span><span class="asp-value">{{ $candidate->constituency ?: '-' }}</span></div>
                                <div class="asp-meta-row"><span class="asp-label">Ward</span><span class="asp-value">{{ $candidate->ward ?: '-' }}</span></div>
                            </div>

                            @if($status === 'pending')
                                <p class="asp-profile-note" style="margin-top:14px;">Your profile is waiting for admin approval. You can prepare available campaign tools while the profile is reviewed.</p>
                            @elseif($status === 'rejected')
                                <p class="asp-profile-note" style="margin-top:14px;">Your profile needs admin attention before it can appear publicly.</p>
                            @endif

                            <form method="POST" action="{{ route('aspirant.social-links.update') }}" class="asp-social-form">
                                @csrf
                                @method('PATCH')
                                <h3><i class="fas fa-share-nodes"></i> Social Media</h3>
                                <div class="asp-social-grid">
                                    @foreach([
                                        'facebook_url' => ['Facebook', 'fa-brands fa-facebook-f', 'https://facebook.com/profile'],
                                        'x_url' => ['X', 'fa-brands fa-x-twitter', 'https://x.com/handle'],
                                        'instagram_url' => ['Instagram', 'fa-brands fa-instagram', 'https://instagram.com/handle'],
                                        'tiktok_url' => ['TikTok', 'fa-brands fa-tiktok', 'https://tiktok.com/@handle'],
                                        'youtube_url' => ['YouTube', 'fa-brands fa-youtube', 'https://youtube.com/@channel'],
                                        'whatsapp_group_url' => ['WhatsApp Group', 'fa-brands fa-whatsapp', 'https://chat.whatsapp.com/invite-code'],
                                    ] as $field => [$label, $icon, $placeholder])
                                        <div class="asp-social-field">
                                            <label for="asp-{{ $field }}"><i class="{{ $icon }}"></i> {{ $label }}</label>
                                            <input type="url" id="asp-{{ $field }}" name="{{ $field }}" value="{{ old($field, $candidate->{$field}) }}" placeholder="{{ $placeholder }}">
                                            @error($field)
                                                <p class="asp-empty">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                                <button type="submit" class="asp-btn primary" style="margin-top:14px;"><i class="fas fa-save"></i> Save Social Links</button>
                            </form>
                            <form method="POST" action="{{ route('aspirant.media.update') }}" enctype="multipart/form-data" class="asp-social-form">
                                @csrf
                                @method('PATCH')
                                <h3><i class="fas fa-photo-film"></i> Campaign Media</h3>
                                <div class="asp-social-grid">
                                    <div class="asp-social-field">
                                        <label for="asp-campaign-video"><i class="fa-brands fa-youtube"></i> Campaign Video</label>
                                        <input type="url" id="asp-campaign-video" name="campaign_video_url" value="{{ old('campaign_video_url', $candidate->campaign_video_url) }}" placeholder="https://www.youtube.com/watch?v=...">
                                        @error('campaign_video_url')<p class="asp-empty">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="asp-social-field">
                                        <label for="asp-campaign-song"><i class="fas fa-music"></i> Campaign Song</label>
                                        <input type="url" id="asp-campaign-song" name="campaign_song_url" value="{{ old('campaign_song_url', $candidate->campaign_song_url) }}" placeholder="https://youtu.be/...">
                                        @error('campaign_song_url')<p class="asp-empty">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="asp-social-field">
                                        <label for="asp-campaign-skiza"><i class="fas fa-volume-high"></i> Campaign Skiza Audio</label>
                                        @if($candidate->campaign_skiza_audio)
                                            <div class="asp-media-preview"><audio controls preload="metadata" src="{{ Storage::url($candidate->campaign_skiza_audio) }}">Your browser does not support audio playback.</audio></div>
                                        @endif
                                        <input type="file" id="asp-campaign-skiza" name="campaign_skiza_audio" accept="audio/mpeg,audio/wav,audio/mp4,audio/aac,audio/ogg,.mp3,.wav,.m4a,.aac,.ogg">
                                        <p class="asp-profile-note">MP3, WAV, M4A, AAC, or OGG up to 20MB.</p>
                                        @error('campaign_skiza_audio')<p class="asp-empty">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="asp-social-field">
                                        <label for="asp-campaign-poster"><i class="fas fa-image"></i> Campaign Poster</label>
                                        @if($candidate->campaign_poster)
                                            <div class="asp-media-preview"><img src="{{ Storage::url($candidate->campaign_poster) }}" alt="Current campaign poster"></div>
                                        @endif
                                        <input type="file" id="asp-campaign-poster" name="campaign_poster" accept="image/jpeg,image/png,image/webp">
                                        <p class="asp-profile-note">JPG, PNG, or WebP up to 5MB.</p>
                                        @error('campaign_poster')<p class="asp-empty">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <button type="submit" class="asp-btn primary" style="margin-top:14px;" data-loading-label="Saving media..."><i class="fas fa-save"></i> Save Campaign Media</button>
                            </form>
                        </div>
                    </div>
                @else
                    <p class="asp-empty">No aspirant profile is linked to this account yet.</p>
                    <a href="{{ route('aspirants.register') }}" class="asp-btn primary" style="margin-top:16px;"><i class="fas fa-user-plus"></i> Register Aspirant Profile</a>
                @endif
            </section>

            @if($isPrimaryAspirant ?? false)
                <section id="team" class="asp-section" data-dashboard-section="team" hidden>
                    <div class="asp-panel-head">
                        <div>
                            <h2><i class="fas fa-user-shield"></i> Campaign Team</h2>
                            <p class="asp-panel-note">Remove fired or inactive team members to revoke their dashboard access.</p>
                        </div>
                        <span class="asp-badge">{{ $teamMembers->count() }} Linked</span>
                    </div>

                    @if($teamMembers->isNotEmpty())
                        <div class="asp-team-list">
                            @foreach($teamMembers as $member)
                                @php($dashboardAccess = (bool) ($member->pivot?->dashboard_access_enabled ?? true))
                                <div class="asp-team-row">
                                    <div>
                                        <div class="asp-team-name">{{ $member->name }}</div>
                                        <div class="asp-team-email">{{ $member->email ?? 'No email' }}</div>
                                    </div>
                                    <div class="asp-team-role">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $member->pivot?->relationship ?? 'Team Member')) }}</div>
                                    <span class="asp-team-access {{ $dashboardAccess ? '' : 'off' }}">{{ $dashboardAccess ? 'Access On' : 'Access Off' }}</span>
                                    <form method="POST" action="{{ route('aspirant.team.destroy', $member) }}" onsubmit="return confirm('Remove this person from your campaign team? Their dashboard access will be revoked.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="asp-team-remove"><i class="fas fa-user-slash"></i> Remove</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="asp-empty">No campaign team members are linked yet.</p>
                    @endif
                </section>
            @endif

            <section id="recent-outreach" class="asp-section" data-dashboard-section="recent-outreach" hidden>
                <div class="asp-panel-head">
                    <div>
                        <h2><i class="{{ $sectionIcon('recent-outreach') }}"></i> Recent Outreach</h2>
                        <p class="asp-panel-note">Latest queued and sent campaign activity.</p>
                    </div>
                    <span class="asp-badge">Live</span>
                </div>

                @if(! empty($recentOutreach))
                    <div class="asp-activity">
                        @foreach($recentOutreach as $item)
                            <div class="asp-activity-row">
                                <div class="asp-activity-icon"><i class="fas fa-comment-sms"></i></div>
                                <div>
                                    <div class="asp-activity-title">{{ $item['channel'] }} {{ strtolower($item['status']) }}</div>
                                    <div class="asp-activity-meta">{{ $item['audience'] }} / {{ number_format($item['recipients']) }} recipients</div>
                                </div>
                                <div class="asp-activity-time">{{ $item['last_sent'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="asp-empty">Queued SMS and campaign activity will appear here after the first outreach job.</p>
                @endif
            </section>

            <section id="poll-snapshot" class="asp-section" data-dashboard-section="poll-snapshot" hidden>
                <div class="asp-panel-head">
                    <div>
                        <h2><i class="{{ $sectionIcon('poll-snapshot') }}"></i> Poll Snapshot</h2>
                        <p class="asp-panel-note">{{ $scopeLabel }}</p>
                    </div>
                    @if($pollSnapshot)
                        <span class="asp-badge">{{ number_format($pollSnapshot['total']) }} Votes</span>
                    @endif
                </div>

                @if($pollSnapshot)
                    <p class="asp-poll-question">{{ $pollSnapshot['question'] }}</p>
                    <div class="asp-bars">
                        @foreach($pollSnapshot['options'] as $index => $option)
                            <div class="asp-bar-row">
                                <span>{{ $option['label'] }}</span>
                                <div class="asp-bar-track"><div class="asp-bar-fill" style="width:{{ $option['percentage'] }}%"></div></div>
                                <strong>{{ $option['percentage'] }}%</strong>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="asp-empty">Poll results will appear here once voters in {{ $scopeLabel }} start responding.</p>
                @endif
            </section>

        </div>
    </div>
</main>
@if($candidate)
<div class="asp-modal{{ $errors->has('cover_photo') ? ' is-open' : '' }}" data-cover-photo-modal aria-hidden="{{ $errors->has('cover_photo') ? 'false' : 'true' }}">
    <div class="asp-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="coverPhotoTitle">
        <div class="asp-modal-head">
            <div>
                <h3 class="asp-modal-title" id="coverPhotoTitle">{{ $candidate->cover_photo ? 'Edit Cover Photo' : 'Add Cover Photo' }}</h3>
                <p class="asp-modal-note">Upload a wide image for the top of your public campaign profile.</p>
            </div>
            <button type="button" class="asp-modal-close" data-cover-photo-close aria-label="Close cover photo form"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('aspirant.cover-photo.update') }}" enctype="multipart/form-data" class="asp-modal-form">
            @csrf
            <div class="asp-cover-modal-preview" data-cover-photo-preview>
                @if($candidate->cover_photo)
                    <img src="{{ Storage::url($candidate->cover_photo) }}" alt="{{ $candidate->name }} cover photo preview">
                @else
                    <span>No cover photo selected</span>
                @endif
            </div>
            <label>
                Cover photo
                <input type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp" required data-cover-photo-input>
            </label>
            @error('cover_photo')
                <p class="asp-profile-note">{{ $message }}</p>
            @enderror
            <button type="submit" class="asp-modal-submit" data-loading-label="Saving cover photo...">Save Cover Photo</button>
        </form>
    </div>
</div>
<div class="asp-modal" data-tool-request-modal aria-hidden="true">
    <div class="asp-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="toolRequestTitle">
        <div class="asp-modal-head">
            <div>
                <h3 class="asp-modal-title" id="toolRequestTitle">Request Tool Activation</h3>
                <p class="asp-modal-note" data-tool-request-reason>Tell the admin what you need activated.</p>
            </div>
            <button type="button" class="asp-modal-close" data-tool-request-close aria-label="Close request form"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('aspirant.tool-activation-requests.store') }}" class="asp-modal-form">
            @csrf
            <input type="hidden" name="tool_key" data-tool-request-key>
            <input type="hidden" name="tool_title" data-tool-request-title>
            <input type="hidden" name="campaign_tool_id" data-tool-request-id>
            <input type="hidden" name="disabled_reason" data-tool-request-disabled-reason>
            <label>
                Message to admin
                <textarea name="message" maxlength="2000" placeholder="Please activate this tool for my campaign."></textarea>
            </label>
            <button type="submit" class="asp-modal-submit" data-loading-label="Sending request...">Send Request</button>
        </form>
    </div>
</div>
@endif
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-priority-card]').forEach((card) => {
        const toggle = card.querySelector('[data-priority-toggle]');
        const editor = card.querySelector('[data-priority-editor]');
        const textarea = editor?.querySelector('textarea');
        if (!toggle || !editor || !textarea) return;
        toggle.addEventListener('change', () => {
            editor.hidden = !toggle.checked;
            textarea.disabled = !toggle.checked;
            card.classList.toggle('is-selected', toggle.checked);
            if (toggle.checked) textarea.focus();
        });
    });
    const sections = Array.from(document.querySelectorAll('[data-dashboard-section]'));
    const links = Array.from(document.querySelectorAll('[data-dashboard-section-link]'));

    const showSection = (sectionName) => {
        const target = sections.find((section) => section.dataset.dashboardSection === sectionName) || sections[0];
        if (!target) return;

        sections.forEach((section) => {
            section.hidden = section !== target;
        });

        links.forEach((link) => {
            link.classList.toggle('active', link.dataset.dashboardSectionLink === target.dataset.dashboardSection);
        });
    };

    links.forEach((link) => {
        link.addEventListener('click', (event) => {
            const sectionName = link.dataset.dashboardSectionLink;
            const target = sections.find((section) => section.dataset.dashboardSection === sectionName);

            // If this markup is ever reused without the matching dashboard
            // section, preserve ordinary link navigation instead of trapping
            // the user on the current page.
            if (!target) return;

            event.preventDefault();
            history.replaceState(null, '', `#${sectionName}`);
            showSection(sectionName);
        });
    });


    const requestModal = document.querySelector('[data-tool-request-modal]');
    const requestButtons = Array.from(document.querySelectorAll('[data-tool-request-open]'));
    const requestClose = document.querySelector('[data-tool-request-close]');
    const requestKey = document.querySelector('[data-tool-request-key]');
    const requestTitle = document.querySelector('[data-tool-request-title]');
    const requestId = document.querySelector('[data-tool-request-id]');
    const requestDisabledReason = document.querySelector('[data-tool-request-disabled-reason]');
    const requestReason = document.querySelector('[data-tool-request-reason]');
    const coverModal = document.querySelector('[data-cover-photo-modal]');
    const coverOpen = document.querySelector('[data-cover-photo-open]');
    const coverClose = document.querySelector('[data-cover-photo-close]');
    const coverInput = document.querySelector('[data-cover-photo-input]');
    const coverPreview = document.querySelector('[data-cover-photo-preview]');

    const closeCoverModal = () => {
        if (!coverModal) return;
        coverModal.classList.remove('is-open');
        coverModal.setAttribute('aria-hidden', 'true');
    };

    coverOpen?.addEventListener('click', () => {
        if (!coverModal) return;
        coverModal.classList.add('is-open');
        coverModal.setAttribute('aria-hidden', 'false');
        coverInput?.focus();
    });
    coverClose?.addEventListener('click', closeCoverModal);
    coverModal?.addEventListener('click', (event) => {
        if (event.target === coverModal) closeCoverModal();
    });
    coverInput?.addEventListener('change', () => {
        const file = coverInput.files?.[0];
        if (!file || !coverPreview) return;
        const imageUrl = URL.createObjectURL(file);
        coverPreview.innerHTML = `<img src="${imageUrl}" alt="Selected cover photo preview">`;
    });

    const closeRequestModal = () => {
        if (!requestModal) return;
        requestModal.classList.remove('is-open');
        requestModal.setAttribute('aria-hidden', 'true');
    };

    requestButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (!requestModal) return;
            requestKey.value = button.dataset.toolKey || '';
            requestTitle.value = button.dataset.toolTitle || '';
            requestId.value = button.dataset.toolId || '';
            requestDisabledReason.value = button.dataset.disabledReason || '';
            requestReason.textContent = button.dataset.disabledReason || 'Tell the admin what you need activated.';
            requestModal.classList.add('is-open');
            requestModal.setAttribute('aria-hidden', 'false');
            requestModal.querySelector('textarea')?.focus();
        });
    });

    requestClose?.addEventListener('click', closeRequestModal);
    requestModal?.addEventListener('click', (event) => {
        if (event.target === requestModal) closeRequestModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeCoverModal();
            closeRequestModal();
        }
    });
    showSection((window.location.hash || '#analytics').replace('#', ''));
});
</script>
@endsection

