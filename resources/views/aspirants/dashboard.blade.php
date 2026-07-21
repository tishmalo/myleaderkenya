@extends('layouts.landing')

@section('title', 'Aspirant Dashboard - My Leader Kenya')

@section('content')
<style>
body { background:#080808; color:#f5f5f0; }
.asp-dash { min-height:100vh; background:#080808; }
.asp-shell { max-width:1380px; margin:0 auto; padding:28px 32px 72px; }
.asp-top { display:flex; align-items:flex-start; justify-content:space-between; gap:24px; margin-bottom:22px; }
.asp-identity { display:flex; align-items:center; gap:18px; min-width:0; }
.asp-avatar { width:86px; height:86px; border-radius:8px; overflow:hidden; background:#242424; display:grid; place-items:center; color:#8b8b8b; flex:0 0 auto; font-size:24px; }
.asp-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
.asp-kicker { margin:0 0 10px; color:rgba(245,245,240,.55); font-size:13px; font-weight:800; letter-spacing:.04em; }
.asp-title { margin:0; font-family:'Oswald',sans-serif; font-size:34px; line-height:1.05; }
.asp-role { margin:9px 0 0; color:rgba(245,245,240,.65); font-size:15px; }
.asp-status { display:inline-flex; align-items:center; gap:8px; min-height:28px; border-radius:999px; padding:0 12px; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.06em; }
.asp-status i { font-size:8px; }
.asp-status.approved { background:rgba(0,168,107,.14); color:#4ade80; }
.asp-status.pending { background:rgba(250,204,21,.14); color:#fde047; }
.asp-status.rejected { background:rgba(239,68,68,.14); color:#fca5a5; }
.asp-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
.asp-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:40px; padding:0 14px; border-radius:8px; border:1px solid rgba(255,255,255,.12); color:white; text-decoration:none; font-weight:800; font-size:13px; background:#151515; white-space:nowrap; }
.asp-btn.primary { background:#d60000; border-color:#d60000; color:white; }
.asp-btn.ghost { color:rgba(245,245,240,.72); }
.asp-btn.danger { color:#ffb4b4; }
.asp-btn:hover { border-color:rgba(255,255,255,.24); }
.asp-alert { margin-bottom:16px; border-radius:8px; padding:13px 15px; color:#fde68a; background:rgba(245,158,11,.12); border:1px solid rgba(245,158,11,.28); }
.asp-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:20px; }
.asp-kpi { display:flex; align-items:center; gap:14px; min-height:76px; border:1px solid rgba(255,255,255,.09); background:#111; border-radius:8px; padding:14px 16px; }
.asp-kpi-icon { width:44px; height:44px; border-radius:8px; display:grid; place-items:center; background:rgba(0,168,107,.12); color:#00A86B; font-size:18px; flex:0 0 auto; }
.asp-kpi span { display:block; color:rgba(245,245,240,.58); font-size:12px; }
.asp-kpi strong { display:block; margin-top:2px; color:white; font-size:24px; line-height:1; font-weight:900; }
.asp-main { display:grid; grid-template-columns:minmax(0,1.28fr) minmax(360px,.72fr); gap:16px; align-items:start; }
.asp-panel { border:1px solid rgba(255,255,255,.09); background:#101010; border-radius:8px; padding:20px; }
.asp-panel + .asp-panel { margin-top:14px; }
.asp-panel-head { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:16px; }
.asp-panel-head > div { min-width:0; }
.asp-panel h2 { margin:0; font-family:'Oswald',sans-serif; font-size:23px; line-height:1.1; }
.asp-panel-note { margin:6px 0 0; color:rgba(245,245,240,.5); font-size:13px; }
.asp-badge { display:inline-flex; align-items:center; gap:7px; min-height:25px; padding:0 10px; border-radius:999px; background:rgba(0,168,107,.13); color:#4ade80; font-size:11px; font-weight:900; text-transform:uppercase; white-space:nowrap; }
.asp-badge.warn { background:rgba(245,158,11,.13); color:#fbbf24; }
.asp-tool-groups { display:grid; gap:20px; }
.asp-tool-group-title { margin:0 0 10px; color:#00A86B; font-size:13px; font-weight:900; }
.asp-tool-group-title.warn { color:#fbbf24; }
.asp-tool-list { border:1px solid rgba(255,255,255,.08); border-radius:8px; overflow:hidden; background:#111; }
.asp-tool-row { display:grid; grid-template-columns:54px minmax(160px,.9fr) minmax(220px,1.4fr) auto; align-items:center; gap:16px; min-height:76px; padding:13px 16px; border-top:1px solid rgba(255,255,255,.07); color:white; text-decoration:none; }
.asp-tool-row:first-child { border-top:0; }
.asp-tool-row:hover { background:#151515; }
.asp-tool-row.disabled { opacity:.55; cursor:not-allowed; pointer-events:none; }
.asp-tool-icon { width:44px; height:44px; border-radius:8px; display:grid; place-items:center; background:rgba(0,168,107,.13); color:#00A86B; font-size:18px; }
.asp-tool-title { font-weight:900; font-size:16px; }
.asp-tool-summary { margin:0; color:rgba(245,245,240,.58); line-height:1.35; font-size:13px; }
.asp-tool-action { display:inline-flex; align-items:center; gap:8px; justify-content:center; min-width:94px; min-height:34px; padding:0 11px; border-radius:8px; border:1px solid rgba(255,255,255,.12); color:#4ade80; font-weight:900; font-size:12px; }
.asp-tool-action.warn { color:#fbbf24; }
.asp-profile-compact { display:flex; align-items:center; gap:14px; margin-bottom:16px; }
.asp-profile-compact .asp-avatar { width:70px; height:70px; }
.asp-name { margin:8px 0 3px; font-size:24px; line-height:1.08; font-weight:900; }
.asp-meta-list { display:grid; gap:0; border:1px solid rgba(255,255,255,.08); border-radius:8px; overflow:hidden; }
.asp-meta-row { display:grid; grid-template-columns:150px 1fr; gap:14px; padding:12px 14px; border-top:1px solid rgba(255,255,255,.07); }
.asp-meta-row:first-child { border-top:0; }
.asp-label { color:rgba(245,245,240,.48); font-size:11px; text-transform:uppercase; letter-spacing:.08em; }
.asp-value { color:rgba(245,245,240,.88); font-weight:800; line-height:1.35; overflow-wrap:anywhere; }
.asp-profile-note { margin:14px 0 0; color:rgba(245,245,240,.62); line-height:1.5; font-size:13px; }
.asp-empty { color:rgba(245,245,240,.55); line-height:1.55; margin:0; }
.asp-activity { display:grid; gap:0; border:1px solid rgba(255,255,255,.08); border-radius:8px; overflow:hidden; }
.asp-activity-row { display:grid; grid-template-columns:42px 1fr auto; gap:12px; align-items:center; padding:12px; border-top:1px solid rgba(255,255,255,.07); }
.asp-activity-row:first-child { border-top:0; }
.asp-activity-icon { width:34px; height:34px; border-radius:8px; display:grid; place-items:center; background:rgba(0,168,107,.13); color:#00A86B; font-size:14px; }
.asp-activity-title { color:white; font-weight:900; font-size:13px; }
.asp-activity-meta { margin-top:2px; color:rgba(245,245,240,.52); font-size:12px; }
.asp-activity-time { color:rgba(245,245,240,.58); font-size:12px; white-space:nowrap; }
.asp-bars { display:grid; gap:12px; }
.asp-poll-question { margin:0 0 14px; color:rgba(245,245,240,.86); font-weight:800; line-height:1.45; }
.asp-bar-row { display:grid; grid-template-columns:minmax(80px,1fr) 1.4fr 62px; align-items:center; gap:10px; color:rgba(245,245,240,.74); font-size:12px; }
.asp-bar-row span { overflow-wrap:anywhere; }
.asp-bar-row strong { text-align:right; color:white; font-size:12px; }
.asp-bar-track { height:8px; border-radius:999px; background:rgba(255,255,255,.08); overflow:hidden; }
.asp-bar-fill { height:100%; border-radius:999px; background:#00A86B; }
.asp-bars .asp-bar-row:nth-child(2) .asp-bar-fill { background:#ef4444; }
.asp-bars .asp-bar-row:nth-child(3) .asp-bar-fill { background:#f59e0b; }
.asp-bars .asp-bar-row:nth-child(4) .asp-bar-fill { background:#3b82f6; }
@media (max-width:1100px) { .asp-main { grid-template-columns:1fr; } .asp-tool-row { grid-template-columns:50px minmax(150px,.8fr) minmax(200px,1.2fr) auto; } }
@media (max-width:760px) { .asp-shell { padding:24px 16px 64px; } .asp-top { flex-direction:column; } .asp-actions { justify-content:flex-start; } .asp-kpis { grid-template-columns:1fr; } .asp-tool-row { grid-template-columns:44px 1fr; } .asp-tool-summary { grid-column:2; } .asp-tool-action { grid-column:2; justify-self:start; } .asp-meta-row { grid-template-columns:1fr; gap:5px; } .asp-activity-row { grid-template-columns:36px 1fr; } .asp-activity-time { grid-column:2; } }
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')

@php
    $status = $candidate?->approval_status ?? 'approved';
    $scopeLabel = $voterScope['label'] ?? 'Kenya';
    $scopeMissing = (bool) ($voterScope['missing'] ?? false);
    $readyModules = collect($toolModules)->where('available', true)->values();
    $setupModules = collect($toolModules)->where('available', false)->values();
@endphp

<main class="asp-dash">
    <div class="asp-shell">
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
                    <p class="asp-role">{{ $candidate?->position?->name ?? 'Campaign workspace' }} · {{ $scopeLabel }}</p>
                </div>
            </div>
            <div class="asp-actions">
                @if($candidate)
                    <span class="asp-status {{ $status }}"><i class="fas fa-circle"></i> {{ ucfirst($status) }}</span>
                @endif
                @if($candidate && $status === 'approved')
                    <a href="{{ route('aspirants.show', $candidate) }}" class="asp-btn primary"><i class="fas fa-eye"></i> Public Profile</a>
                @endif
                <a href="{{ route('aspirant.tokens.index') }}" class="asp-btn primary"><i class="fas fa-coins"></i> Buy Tokens</a>
                <a href="{{ route('campaign-tools.public') }}" class="asp-btn ghost"><i class="fas fa-toolbox"></i> All Tools</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="asp-btn danger"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
        </div>

        @if(session('warning'))
            <div class="asp-alert">{{ session('warning') }}</div>
        @endif

        @if($scopeMissing)
            <div class="asp-alert">{{ $voterScope['message'] ?? 'Ask an admin to complete your campaign jurisdiction before using voter-facing tools.' }}</div>
        @endif

        <section class="asp-kpis" aria-label="Campaign summary">
            <div class="asp-kpi"><div class="asp-kpi-icon"><i class="fas fa-users"></i></div><div><span>Voters</span><strong>{{ number_format($dashboardStats['scoped_voters'] ?? 0) }}</strong></div></div>
            <div class="asp-kpi"><div class="asp-kpi-icon"><i class="fas fa-phone"></i></div><div><span>Reachable phones</span><strong>{{ number_format($dashboardStats['reachable_voters'] ?? 0) }}</strong></div></div>
            <div class="asp-kpi"><div class="asp-kpi-icon"><i class="fas fa-square-poll-vertical"></i></div><div><span>Active polls</span><strong>{{ number_format($dashboardStats['active_polls'] ?? 0) }}</strong></div></div>
            <div class="asp-kpi"><div class="asp-kpi-icon"><i class="fas fa-coins"></i></div><div><span>Token balance</span><strong>{{ number_format($tokenWallet?->balance ?? 0) }}</strong></div></div>
        </section>

        <div class="asp-main">
            <section class="asp-panel">
                <div class="asp-panel-head">
                    <div>
                        <h2>Campaign Tools</h2>
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
                                    <a href="#" class="asp-tool-row disabled" title="{{ $module['disabled_reason'] ?? '' }}" aria-disabled="true">
                                        <div class="asp-tool-icon"><i class="{{ str_starts_with($module['icon'], 'fa-brands') ? $module['icon'] : 'fas ' . $module['icon'] }}"></i></div>
                                        <div class="asp-tool-title">{{ $module['title'] }}</div>
                                        <p class="asp-tool-summary">{{ $module['disabled_reason'] ?: $module['summary'] }}</p>
                                        <span class="asp-tool-action warn">Setup <i class="fas fa-chevron-right"></i></span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <aside>
                <section class="asp-panel">
                    <div class="asp-panel-head">
                        <h2>Candidate Profile</h2>
                        @if($candidate)
                            <span class="asp-status {{ $status }}"><i class="fas fa-circle"></i> {{ ucfirst($status) }}</span>
                        @endif
                    </div>
                    @if($candidate)
                        <div class="asp-profile-compact">
                            <div class="asp-avatar">
                                @if($candidate->profile_picture)
                                    <img src="{{ Storage::url($candidate->profile_picture) }}" alt="{{ $candidate->name }}">
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            </div>
                            <div>
                                <h3 class="asp-name">{{ $candidate->name }}</h3>
                                <p class="asp-role">{{ $candidate->position->name ?? 'Aspirant' }}</p>
                            </div>
                        </div>

                        <div class="asp-meta-list">
                            <div class="asp-meta-row"><span class="asp-label">Political Party</span><span class="asp-value">{{ $candidate->politicalParty->name ?? 'Independent / Not set' }}</span></div>
                            <div class="asp-meta-row"><span class="asp-label">County</span><span class="asp-value">{{ $candidate->county ?: '-' }}</span></div>
                            <div class="asp-meta-row"><span class="asp-label">Constituency</span><span class="asp-value">{{ $candidate->constituency ?: '-' }}</span></div>
                            <div class="asp-meta-row"><span class="asp-label">Ward</span><span class="asp-value">{{ $candidate->ward ?: '-' }}</span></div>
                        </div>

                        @if($status === 'pending')
                            <p class="asp-profile-note">Your profile is waiting for admin approval. You can prepare available campaign tools while the profile is reviewed.</p>
                        @elseif($status === 'rejected')
                            <p class="asp-profile-note">Your profile needs admin attention before it can appear publicly.</p>
                        @endif
                    @else
                        <p class="asp-empty">No aspirant profile is linked to this account yet.</p>
                        <a href="{{ route('aspirants.register') }}" class="asp-btn primary" style="margin-top:16px;"><i class="fas fa-user-plus"></i> Register Aspirant Profile</a>
                    @endif
                </section>

                <section class="asp-panel">
                    <div class="asp-panel-head">
                        <h2>Recent Outreach</h2>
                        <span class="asp-badge">Live</span>
                    </div>
                    @if(! empty($recentOutreach))
                        <div class="asp-activity">
                            @foreach($recentOutreach as $item)
                                <div class="asp-activity-row">
                                    <div class="asp-activity-icon"><i class="fas fa-comment-sms"></i></div>
                                    <div>
                                        <div class="asp-activity-title">{{ $item['channel'] }} {{ strtolower($item['status']) }}</div>
                                        <div class="asp-activity-meta">{{ $item['audience'] }} · {{ number_format($item['recipients']) }} recipients</div>
                                    </div>
                                    <div class="asp-activity-time">{{ $item['last_sent'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="asp-empty">Queued SMS and campaign activity will appear here after the first outreach job.</p>
                    @endif
                </section>

                <section class="asp-panel">
                    <div class="asp-panel-head">
                        <div>
                            <h2>Poll Snapshot</h2>
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
            </aside>
        </div>
    </div>
</main>
@endsection

