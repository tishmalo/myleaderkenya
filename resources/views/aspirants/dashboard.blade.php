@extends('layouts.landing')

@section('title', 'Aspirant Dashboard - My Leader Kenya')

@section('content')
<style>
body { background:#080808; color:#f5f5f0; }
.asp-dash { min-height:100vh; background:#080808; }
.asp-shell { max-width:1360px; margin:0 auto; padding:30px 28px 80px; }
.asp-hero { display:flex; align-items:flex-end; justify-content:space-between; gap:20px; margin-bottom:18px; }
.asp-kicker { color:#00A86B; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.14em; }
.asp-title { margin:8px 0 0; font-family:'Oswald',sans-serif; font-size:40px; line-height:1; }
.asp-scope { margin-top:10px; color:rgba(245,245,240,.64); font-size:14px; }
.asp-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.asp-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:0 15px; border-radius:8px; border:1px solid rgba(255,255,255,.12); color:white; text-decoration:none; font-weight:900; font-size:13px; background:#151515; white-space:nowrap; }
.asp-btn.primary { background:#d60000; border-color:#d60000; color:white; }
.asp-btn.danger { color:#ffb4b4; }
.asp-alert { margin-bottom:16px; border-radius:8px; padding:14px 16px; color:#fde68a; background:rgba(245,158,11,.12); border:1px solid rgba(245,158,11,.28); }
.asp-stat-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
.asp-stat { min-height:98px; border:1px solid rgba(255,255,255,.08); background:#121212; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between; }
.asp-stat span { color:rgba(245,245,240,.52); font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.1em; }
.asp-stat strong { margin-top:8px; display:block; color:white; font-size:26px; line-height:1.1; font-weight:900; overflow-wrap:anywhere; }
.asp-stat.scope strong { font-size:20px; line-height:1.2; }
.asp-stat small { color:#00A86B; font-size:12px; font-weight:800; }
.asp-main { display:grid; grid-template-columns:minmax(0,1.12fr) minmax(360px,.88fr); gap:18px; align-items:start; }
.asp-panel { border:1px solid rgba(255,255,255,.08); background:#111; border-radius:8px; padding:22px; }
.asp-panel-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; }
.asp-panel-head > div { min-width:0; }
.asp-panel h2 { margin:0; font-family:'Oswald',sans-serif; font-size:25px; line-height:1.1; }
.asp-panel-note { margin:0; color:rgba(245,245,240,.5); font-size:13px; }
.asp-tools { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
.asp-tool { min-height:218px; display:flex; flex-direction:column; gap:13px; border:1px solid rgba(255,255,255,.08); background:#151515; border-radius:8px; padding:18px; text-decoration:none; color:white; transition:transform .18s,border-color .18s,background .18s; }
.asp-tool:hover { transform:translateY(-2px); border-color:rgba(0,168,107,.55); background:#181818; }
.asp-tool.disabled { opacity:.48; cursor:not-allowed; pointer-events:none; }
.asp-tool.disabled:hover { transform:none; border-color:rgba(255,255,255,.08); }
.asp-tool-icon { width:44px; height:44px; border-radius:8px; display:grid; place-items:center; background:rgba(0,168,107,.13); color:#00A86B; font-size:18px; }
.asp-tool h3 { margin:0; font-family:'Oswald',sans-serif; font-size:24px; line-height:1.15; }
.asp-tool p { margin:0; color:rgba(245,245,240,.62); line-height:1.45; font-size:14px; }
.asp-tool-foot { margin-top:auto; display:flex; align-items:center; justify-content:space-between; gap:12px; color:#00A86B; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.08em; }
.asp-chip { color:rgba(245,245,240,.62); font-size:11px; border:1px solid rgba(255,255,255,.13); border-radius:999px; padding:5px 10px; }
.asp-profile-card { border:1px solid rgba(255,255,255,.08); background:#151515; border-radius:8px; padding:22px; }
.asp-profile { display:flex; gap:18px; align-items:center; }
.asp-avatar { width:100px; height:100px; border-radius:8px; overflow:hidden; background:#242424; display:grid; place-items:center; color:#8b8b8b; flex:0 0 auto; font-size:26px; }
.asp-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
.asp-status { display:inline-flex; align-items:center; gap:8px; border-radius:999px; padding:8px 14px; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.08em; }
.asp-status.approved { background:rgba(0,168,107,.14); color:#4ade80; }
.asp-status.pending { background:rgba(250,204,21,.14); color:#fde047; }
.asp-status.rejected { background:rgba(239,68,68,.14); color:#fca5a5; }
.asp-name { margin:12px 0 4px; font-size:28px; line-height:1.1; font-weight:900; }
.asp-role { margin:0; color:rgba(245,245,240,.62); font-size:15px; }
.asp-meta { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; margin-top:20px; }
.asp-meta div { min-height:92px; background:#101010; border:1px solid rgba(255,255,255,.07); border-radius:8px; padding:15px; }
.asp-label { display:block; color:rgba(245,245,240,.48); font-size:11px; text-transform:uppercase; letter-spacing:.1em; margin-bottom:8px; }
.asp-value { color:white; font-weight:900; line-height:1.35; }
.asp-side-stack { display:grid; gap:14px; }
.asp-table { width:100%; border-collapse:collapse; }
.asp-table th { color:rgba(245,245,240,.45); font-size:11px; text-transform:uppercase; letter-spacing:.08em; text-align:left; padding:0 0 10px; }
.asp-table td { border-top:1px solid rgba(255,255,255,.07); padding:12px 0; color:rgba(245,245,240,.8); font-size:13px; }
.asp-badge { display:inline-flex; align-items:center; min-height:24px; padding:0 9px; border-radius:999px; background:rgba(0,168,107,.13); color:#4ade80; font-size:11px; font-weight:900; text-transform:uppercase; }
.asp-empty { color:rgba(245,245,240,.55); line-height:1.55; margin:0; }
.asp-profile-note { margin:16px 0 0; color:rgba(245,245,240,.62); line-height:1.55; font-size:13px; }
.asp-bars { display:grid; gap:12px; }
.asp-poll-question { margin:0 0 14px; color:rgba(245,245,240,.86); font-weight:800; line-height:1.45; }
.asp-bar-row { display:grid; grid-template-columns:minmax(82px,1fr) 1.5fr 70px; align-items:center; gap:10px; color:rgba(245,245,240,.74); font-size:13px; }
.asp-bar-row span { overflow-wrap:anywhere; }
.asp-bar-row strong { text-align:right; color:white; font-size:12px; }
.asp-bar-track { height:9px; border-radius:999px; background:rgba(255,255,255,.08); overflow:hidden; }
.asp-bar-fill { height:100%; border-radius:999px; background:#00A86B; }
.asp-bars .asp-bar-row:nth-child(2) .asp-bar-fill { background:#ef4444; }
.asp-bars .asp-bar-row:nth-child(3) .asp-bar-fill { background:#f59e0b; }
.asp-bars .asp-bar-row:nth-child(4) .asp-bar-fill { background:#3b82f6; }
@media (max-width:1100px) { .asp-stat-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } .asp-main { grid-template-columns:1fr; } }
@media (max-width:760px) { .asp-shell { padding:24px 16px 64px; } .asp-hero { align-items:flex-start; flex-direction:column; } .asp-stat-grid,.asp-tools,.asp-meta { grid-template-columns:1fr; } .asp-profile { align-items:flex-start; flex-direction:column; } }
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')

@php
    $status = $candidate?->approval_status ?? 'approved';
    $scopeLabel = $voterScope['label'] ?? 'Kenya';
    $smsReady = collect($toolModules)->firstWhere('key', 'bulk-sms')['available'] ?? false;
    $scopeMissing = (bool) ($voterScope['missing'] ?? false);
@endphp

<main class="asp-dash">
    <div class="asp-shell">
        <div class="asp-hero">
            <div>
                <div class="asp-kicker">Aspirant Workspace</div>
                <h1 class="asp-title">Campaign Dashboard</h1>
                <div class="asp-scope">Voting bloc: {{ $scopeLabel }}</div>
            </div>
            <div class="asp-actions">
                <a href="{{ route('campaign-tools.public') }}" class="asp-btn"><i class="fas fa-toolbox"></i> All Tools</a>
                @if($candidate && $status === 'approved')
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

        @if($scopeMissing)
            <div class="asp-alert">{{ $voterScope['message'] ?? 'Ask an admin to complete your campaign jurisdiction before using voter-facing tools.' }}</div>
        @endif

        <section class="asp-stat-grid" aria-label="Campaign summary">
            <div class="asp-stat scope"><span>Voting Bloc</span><strong>{{ $scopeLabel }}</strong><small>Locked audience</small></div>
            <div class="asp-stat"><span>Scoped Voters</span><strong>{{ number_format($dashboardStats['scoped_voters'] ?? 0) }}</strong><small>Registered voters</small></div>
            <div class="asp-stat"><span>Reachable Phones</span><strong>{{ number_format($dashboardStats['reachable_voters'] ?? 0) }}</strong><small>SMS eligible</small></div>
            <div class="asp-stat"><span>Active Polls</span><strong>{{ number_format($dashboardStats['active_polls'] ?? 0) }}</strong><small>Bloc analytics</small></div>
            <div class="asp-stat"><span>Bulk SMS</span><strong>{{ $smsReady ? 'Ready' : 'Setup' }}</strong><small>{{ $smsReady ? 'Credentials active' : 'Admin required' }}</small></div>
        </section>

        <div class="asp-main">
            <section class="asp-panel">
                <div class="asp-panel-head">
                    <div>
                        <h2>Campaign Tools</h2>
                        <p class="asp-panel-note">Only enabled tools open. Disabled tools stay visible for setup tracking.</p>
                    </div>
                    <span class="asp-badge">{{ $dashboardStats['enabled_tools'] ?? 0 }} Ready</span>
                </div>

                <div class="asp-tools">
                    @foreach($toolModules as $module)
                        <a href="{{ $module['url'] }}" class="asp-tool {{ $module['available'] ? '' : 'disabled' }}" title="{{ $module['disabled_reason'] ?? '' }}" aria-disabled="{{ $module['available'] ? 'false' : 'true' }}">
                            <div class="asp-tool-icon"><i class="{{ str_starts_with($module['icon'], 'fa-brands') ? $module['icon'] : 'fas ' . $module['icon'] }}"></i></div>
                            <h3>{{ $module['title'] }}</h3>
                            <p>{{ $module['summary'] }}</p>
                            <div class="asp-tool-foot">
                                <span>{{ $module['available'] ? 'Open Tool' : 'Setup Required' }}</span>
                                <span class="asp-chip">{{ $module['available'] ? 'Ready' : 'Setup' }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <aside class="asp-side-stack">
                <section class="asp-profile-card">
                    @if($candidate)
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
                                <h2 class="asp-name">{{ $candidate->name }}</h2>
                                <p class="asp-role">{{ $candidate->position->name ?? 'Aspirant' }}</p>
                            </div>
                        </div>

                        <div class="asp-meta">
                            <div><span class="asp-label">Political Party</span><span class="asp-value">{{ $candidate->politicalParty->name ?? 'Independent / Not set' }}</span></div>
                            <div><span class="asp-label">County</span><span class="asp-value">{{ $candidate->county ?: '-' }}</span></div>
                            <div><span class="asp-label">Constituency</span><span class="asp-value">{{ $candidate->constituency ?: '-' }}</span></div>
                            <div><span class="asp-label">Ward</span><span class="asp-value">{{ $candidate->ward ?: '-' }}</span></div>
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
                        <table class="asp-table">
                            <thead><tr><th>Channel</th><th>Audience</th><th>Status</th><th>Sent</th></tr></thead>
                            <tbody>
                                @foreach($recentOutreach as $item)
                                    <tr>
                                        <td>{{ $item['channel'] }}</td>
                                        <td>{{ $item['audience'] }}<br><span style="color:rgba(245,245,240,.42);">{{ number_format($item['recipients']) }} recipients</span></td>
                                        <td><span class="asp-badge">{{ $item['status'] }}</span></td>
                                        <td>{{ $item['last_sent'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                                    <strong>{{ $option['percentage'] }}% / {{ number_format($option['count']) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="asp-empty">Poll results will appear here once you publish a poll and voters in {{ $scopeLabel }} start responding.</p>
                    @endif
                </section>
            </aside>
        </div>
    </div>
</main>
@endsection
