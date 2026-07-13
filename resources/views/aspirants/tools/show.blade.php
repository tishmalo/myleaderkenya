@extends('layouts.landing')

@section('title', $module['title'] . ' - Aspirant Dashboard')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:wght@400;500;600;700;800&display=swap');
body { font-family:'Barlow',sans-serif; background:#090909; color:#f5f5f0; }
h1,h2,h3 { font-family:'Oswald',sans-serif; }
.flag-stripe { height:4px; background:linear-gradient(90deg,#006600 33%,#111 33% 66%,#BB0000 66%); }
.tool-page { min-height:100vh; background:#090909; }
.tool-wrap { max-width:1280px; margin:0 auto; padding:42px 28px 84px; }
.tool-top { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; margin-bottom:24px; }
.tool-kicker { color:#00A86B; font-size:12px; font-weight:800; letter-spacing:.18em; text-transform:uppercase; }
.tool-title { margin:8px 0 0; color:#fff; font-size:42px; line-height:1; }
.tool-sub { margin:12px 0 0; max-width:720px; color:rgba(245,245,240,.62); line-height:1.55; }
.tool-btn { display:inline-flex; align-items:center; gap:8px; border-radius:8px; border:1px solid rgba(255,255,255,.12); padding:11px 14px; color:#f5f5f0; text-decoration:none; font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; background:#141414; }
.tool-btn.primary { border-color:rgba(0,168,107,.45); background:#006600; }
.tool-grid { display:grid; grid-template-columns:minmax(0,1.5fr) minmax(320px,.8fr); gap:22px; }
.tool-panel { border:1px solid rgba(255,255,255,.08); border-radius:8px; background:#121212; padding:22px; }
.tool-panel h2 { margin:0 0 18px; font-size:24px; }
.tool-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-bottom:20px; }
.tool-stat { border:1px solid rgba(255,255,255,.07); border-radius:8px; background:#0d0d0d; padding:16px; }
.tool-label { display:block; margin-bottom:7px; color:rgba(245,245,240,.48); font-size:11px; font-weight:800; letter-spacing:.11em; text-transform:uppercase; }
.tool-value { color:#fff; font-size:28px; font-weight:800; }
.tool-note { color:rgba(245,245,240,.62); line-height:1.6; }
.tool-alert { border:1px solid rgba(245,158,11,.3); border-radius:8px; background:rgba(245,158,11,.12); color:#fde68a; padding:16px; line-height:1.55; }
.tool-success { border:1px solid rgba(34,197,94,.3); border-radius:8px; background:rgba(34,197,94,.12); color:#bbf7d0; padding:16px; line-height:1.55; margin-bottom:18px; }
.poll-result { margin-top:14px; border-top:1px solid rgba(255,255,255,.07); padding-top:14px; }
.poll-bar { height:8px; border-radius:999px; overflow:hidden; background:#242424; margin-top:7px; }
.poll-bar span { display:block; height:100%; background:#00A86B; }
.tool-form { display:grid; gap:14px; }
.tool-form label { display:grid; gap:7px; color:rgba(245,245,240,.58); font-size:12px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
.tool-form input,.tool-form textarea,.tool-form select { width:100%; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:#0b0b0b; color:#fff; padding:12px 13px; font:inherit; }
.tool-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:4px; }
.tool-table { width:100%; border-collapse:collapse; }
.tool-table th,.tool-table td { padding:12px 10px; border-top:1px solid rgba(255,255,255,.06); text-align:left; font-size:13px; }
.tool-table th { color:rgba(245,245,240,.48); font-size:11px; text-transform:uppercase; letter-spacing:.09em; }
.tool-table td { color:rgba(245,245,240,.74); }
.tool-empty { color:rgba(245,245,240,.5); line-height:1.6; }
@media (max-width:980px) { .tool-grid,.tool-stats { grid-template-columns:1fr; } .tool-top { flex-direction:column; } }
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')

<main class="tool-page">
    <div class="tool-wrap">
        <div class="tool-top">
            <div>
                <div class="tool-kicker">Aspirant Tool</div>
                <h1 class="tool-title">{{ $module['title'] }}</h1>
                <p class="tool-sub">{{ $module['summary'] }}</p>
            </div>
            <a href="/aspirant/dashboard" class="tool-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div>

        @if(session('success'))
            <div class="tool-success">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
            <div class="tool-alert" style="margin-bottom:18px;">{{ session('warning') }}</div>
        @endif

        <div class="tool-grid">
            <section class="tool-panel">
                <h2>Workspace</h2>

                @if($isBlocked)
                    <div class="tool-alert">{{ $scope['message'] }}</div>
                @else
                    @if($module['voter_facing'])
                        <div class="tool-stats">
                            <div class="tool-stat">
                                <span class="tool-label">Voting Bloc</span>
                                <div class="tool-value" style="font-size:20px;">{{ $scope['label'] }}</div>
                            </div>
                            <div class="tool-stat">
                                <span class="tool-label">Scoped Voters</span>
                                <div class="tool-value">{{ number_format($voterCount ?? 0) }}</div>
                            </div>
                            <div class="tool-stat">
                                <span class="tool-label">Access Rule</span>
                                <div class="tool-value" style="font-size:20px;">{{ ucfirst($scope['type']) }}</div>
                            </div>
                        </div>
                    @endif

                    @if($module['key'] === 'campaign-website')
                        <p class="tool-note">Manage the public-facing campaign profile for {{ $candidate->name }}. This tool uses aspirant profile data and does not expose voter records.</p>
                        <div class="tool-actions">
                            @if(($candidate->approval_status ?? 'approved') === 'approved')
                                <a href="{{ route('aspirants.show', $candidate) }}" class="tool-btn primary"><i class="fas fa-eye"></i> View Public Profile</a>
                            @endif
                            <a href="/aspirant/dashboard" class="tool-btn"><i class="fas fa-user-pen"></i> Review Profile</a>
                        </div>
                    @elseif($module['key'] === 'opinion-polls')
                        <form class="tool-form">
                            <label>Poll Question
                                <input type="text" value="What issue should our campaign prioritize first?">
                            </label>
                            <label>Audience
                                <input type="text" value="{{ $scope['label'] }}" readonly>
                            </label>
                            <label>Options
                                <textarea rows="5">Roads and transport&#10;Water access&#10;Jobs and business support&#10;Education services</textarea>
                            </label>
                            <div class="tool-actions">
                                <button type="button" class="tool-btn primary"><i class="fas fa-save"></i> Save Draft</button>
                                <button type="button" class="tool-btn"><i class="fas fa-chart-simple"></i> Preview Poll</button>
                            </div>
                        </form>
                    @elseif($module['key'] === 'bulk-sms')
                        <form class="tool-form">
                            <label>Recipients
                                <input type="text" value="{{ number_format($voterCount ?? 0) }} registered voters in {{ $scope['label'] }}" readonly>
                            </label>
                            <label>Message
                                <textarea rows="6" placeholder="Write a focused SMS update for voters in your bloc."></textarea>
                            </label>
                            <div class="tool-actions">
                                <button type="button" class="tool-btn primary"><i class="fas fa-save"></i> Save Draft</button>
                                <button type="button" class="tool-btn"><i class="fas fa-paper-plane"></i> Queue Review</button>
                            </div>
                        </form>
                    @elseif($module['key'] === 'bulk-whatsapp')
                        <form class="tool-form">
                            <label>Group / Segment
                                <input type="text" value="{{ $scope['label'] }} WhatsApp audience" readonly>
                            </label>
                            <label>Campaign Update
                                <textarea rows="6" placeholder="Prepare a WhatsApp update for your scoped campaign audience."></textarea>
                            </label>
                            <div class="tool-actions">
                                <button type="button" class="tool-btn primary"><i class="fas fa-save"></i> Save Update</button>
                                <button type="button" class="tool-btn"><i class="fab fa-whatsapp"></i> Prepare Contacts</button>
                            </div>
                        </form>
                    @elseif($module['key'] === 'call-center')
                        <form class="tool-form">
                            <label>Call Script
                                <textarea rows="6">Hello, this is the campaign team for {{ $candidate->name }}. We are listening to voters in {{ $scope['label'] }} and would like to hear what matters most to you.</textarea>
                            </label>
                            <label>Callback Priority
                                <select><option>Undecided voters</option><option>Registered supporters</option><option>Volunteer leads</option></select>
                            </label>
                            <div class="tool-actions">
                                <button type="button" class="tool-btn primary"><i class="fas fa-save"></i> Save Script</button>
                                <button type="button" class="tool-btn"><i class="fas fa-phone"></i> Start Call List</button>
                            </div>
                        </form>
                    @else
                        <p class="tool-note">Use this workspace to inspect and organize registered voters inside {{ $scope['label'] }}. The list below is already restricted to your voting bloc.</p>
                    @endif
                @endif
            </section>

            <aside class="tool-panel">
                <h2>{{ $module['voter_facing'] ? 'Scoped Contacts' : 'Profile Context' }}</h2>

                @if(! $module['voter_facing'])
                    <p class="tool-note"><strong>{{ $candidate->name }}</strong><br>{{ $candidate->position->name ?? 'Aspirant' }}<br>{{ collect([$candidate->county, $candidate->constituency, $candidate->ward])->filter()->join(' / ') ?: 'Jurisdiction not set' }}</p>
                @elseif($isBlocked)
                    <p class="tool-empty">Voter contacts are hidden until the aspirant jurisdiction is complete.</p>
                @elseif($recentVoters->isEmpty())
                    <p class="tool-empty">No registered voters found in {{ $scope['label'] }} yet.</p>
                @else
                    <div style="overflow-x:auto;">
                        <table class="tool-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Ward</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentVoters as $voter)
                                    <tr>
                                        <td>{{ $voter->name ?: $voter->username }}</td>
                                        <td>{{ $voter->phone ?: '-' }}</td>
                                        <td>{{ $voter->ward ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</main>
@endsection
