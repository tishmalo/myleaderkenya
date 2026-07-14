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
.tool-btn[disabled] { opacity:.7; cursor:wait; }
.tool-spinner { display:none; width:14px; height:14px; border:2px solid rgba(255,255,255,.45); border-top-color:#fff; border-radius:999px; animation:tool-spin .8s linear infinite; }
.tool-btn.loading .tool-spinner { display:inline-block; }
@keyframes tool-spin { to { transform:rotate(360deg); } }
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
.poll-preview { display:none; margin-top:18px; border:1px solid rgba(0,168,107,.25); border-radius:8px; background:#0b0f0d; padding:18px; }
.poll-preview.is-visible { display:block; }
.poll-preview h3 { margin:0 0 10px; font-size:20px; }
.poll-preview p { margin:0 0 14px; color:rgba(245,245,240,.84); font-weight:800; line-height:1.45; }
.poll-preview ol { margin:0; padding-left:20px; color:rgba(245,245,240,.72); line-height:1.75; }
.poll-list { display:grid; gap:12px; margin-top:22px; }
.poll-card { border:1px solid rgba(255,255,255,.07); border-radius:8px; background:#0d0d0d; padding:15px; }
.poll-card-top { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:10px; }
.poll-card h3 { margin:0; font-size:18px; color:#fff; line-height:1.3; }
.poll-status { border-radius:999px; padding:4px 8px; color:#bbf7d0; background:rgba(34,197,94,.12); font-size:10px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
.poll-status.draft { color:#fde68a; background:rgba(245,158,11,.12); }
.poll-options { margin:0; padding-left:20px; color:rgba(245,245,240,.68); line-height:1.7; }
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
                        @if($websiteRequest)
                            <div class="tool-success">
                                Latest request status: <strong>{{ str_replace('_', ' ', ucfirst($websiteRequest->status)) }}</strong>
                                @if($websiteRequest->admin_notes)
                                    <br>{{ $websiteRequest->admin_notes }}
                                @endif
                            </div>
                        @endif

                        <form class="tool-form" method="POST" action="{{ route('aspirant.tools.campaign-website.request') }}" data-loading-form>
                            @csrf
                            <label>Candidate / Campaign Name
                                <input type="text" name="candidate_name" value="{{ old('candidate_name', $websiteRequest->candidate_name ?? $candidate->name) }}" required maxlength="255">
                            </label>
                            @error('candidate_name')<div class="tool-alert">{{ $message }}</div>@enderror

                            <label>Phone
                                <input type="text" name="phone" value="{{ old('phone', $websiteRequest->phone ?? $candidate->phone) }}" maxlength="50">
                            </label>
                            @error('phone')<div class="tool-alert">{{ $message }}</div>@enderror

                            <label>Email
                                <input type="email" name="email" value="{{ old('email', $websiteRequest->email ?? $candidate->email) }}" maxlength="255">
                            </label>
                            @error('email')<div class="tool-alert">{{ $message }}</div>@enderror

                            <label>Preferred Domain / Website Name
                                <input type="text" name="preferred_domain" value="{{ old('preferred_domain', $websiteRequest->preferred_domain ?? '') }}" placeholder="example.co.ke or candidate name" maxlength="255">
                            </label>
                            @error('preferred_domain')<div class="tool-alert">{{ $message }}</div>@enderror

                            <label>Website Package
                                <select name="website_type" required>
                                    @php($selectedType = old('website_type', $websiteRequest->website_type ?? 'standard'))
                                    <option value="standard" {{ $selectedType === 'standard' ? 'selected' : '' }}>Standard campaign website</option>
                                    <option value="premium" {{ $selectedType === 'premium' ? 'selected' : '' }}>Premium campaign website</option>
                                    <option value="custom" {{ $selectedType === 'custom' ? 'selected' : '' }}>Custom website</option>
                                </select>
                            </label>
                            @error('website_type')<div class="tool-alert">{{ $message }}</div>@enderror

                            <label>Reference Link
                                <input type="url" name="reference_url" value="{{ old('reference_url', $websiteRequest->reference_url ?? '') }}" placeholder="https://..." maxlength="500">
                            </label>
                            @error('reference_url')<div class="tool-alert">{{ $message }}</div>@enderror

                            <label>Notes For Admin
                                <textarea name="notes" rows="6" maxlength="2000" placeholder="Share colors, sections, donation links, photos needed, timeline, or any special request.">{{ old('notes', $websiteRequest->notes ?? '') }}</textarea>
                            </label>
                            @error('notes')<div class="tool-alert">{{ $message }}</div>@enderror

                            <div class="tool-actions">
                                <button type="submit" class="tool-btn primary" data-loading-button data-loading-text="Submitting..."><span class="tool-spinner" aria-hidden="true"></span><i class="fas fa-paper-plane" data-loading-icon></i> <span data-loading-label>Submit Request</span></button>
                                <a href="{{ route('aspirant.campaign-website.samples') }}" class="tool-btn"><i class="fas fa-images"></i> View Samples</a>
                            </div>
                        </form>
                    @elseif($module['key'] === 'opinion-polls')
                        <form class="tool-form" method="POST" action="{{ route('aspirant.tools.polls.store') }}" data-loading-form data-poll-form>
                            @csrf
                            <label>Poll Question
                                <input type="text" name="question" value="{{ old('question', 'What issue should our campaign prioritize first?') }}" required maxlength="255" data-poll-question>
                            </label>
                            @error('question')
                                <div class="tool-alert">{{ $message }}</div>
                            @enderror
                            <label>Audience
                                <input type="text" value="{{ $scope['label'] }}" readonly>
                            </label>
                            <label>Options
                                <textarea rows="5" name="options" required data-poll-options>{{ old('options', "Roads and transport\nWater access\nJobs and business support\nEducation services") }}</textarea>
                            </label>
                            @error('options')
                                <div class="tool-alert">{{ $message }}</div>
                            @enderror
                            <div class="tool-actions">
                                <button type="submit" name="status" value="draft" class="tool-btn primary" data-loading-button data-loading-text="Saving..."><span class="tool-spinner" aria-hidden="true"></span><i class="fas fa-save" data-loading-icon></i> <span data-loading-label>Save Draft</span></button>
                                <button type="submit" name="status" value="published" class="tool-btn"><i class="fas fa-paper-plane"></i> Publish Poll</button>
                                <button type="button" class="tool-btn" data-preview-poll><i class="fas fa-chart-simple"></i> Preview Poll</button>
                            </div>
                            <div class="poll-preview" data-poll-preview aria-live="polite">
                                <h3>Poll Preview</h3>
                                <p data-preview-question></p>
                                <ol data-preview-options></ol>
                            </div>
                        </form>

                        @if($polls->isNotEmpty())
                            <div class="poll-list">
                                @foreach($polls as $poll)
                                    @php
                                        $totalResponses = $poll->responses->count();
                                    @endphp
                                    <article class="poll-card">
                                        <div class="poll-card-top">
                                            <h3>{{ $poll->question }}</h3>
                                            <span class="poll-status {{ $poll->status === 'draft' ? 'draft' : '' }}">{{ $poll->status }}</span>
                                        </div>
                                        <ol class="poll-options">
                                            @foreach($poll->options ?? [] as $index => $option)
                                                @php
                                                    $count = $poll->responses->where('option_index', $index)->count();
                                                    $percent = $totalResponses > 0 ? round(($count / $totalResponses) * 100) : 0;
                                                @endphp
                                                <li>
                                                    {{ $option }} - {{ number_format($count) }} votes
                                                    <div class="poll-bar"><span style="width:{{ $percent }}%;"></span></div>
                                                </li>
                                            @endforeach
                                        </ol>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    @elseif($module['key'] === 'bulk-sms')
                        <form class="tool-form" method="POST" action="{{ route('aspirant.tools.bulk-sms.send') }}" data-loading-form>
                            @csrf
                            <label>Recipients
                                <input type="text" value="{{ number_format($voterCount ?? 0) }} registered voters in {{ $scope['label'] }}" readonly>
                            </label>
                            <label>Message
                                <textarea name="message" rows="6" maxlength="918" required placeholder="Write a focused SMS update for voters in your bloc.">{{ old('message') }}</textarea>
                            </label>
                            @error('message')
                                <div class="tool-alert">{{ $message }}</div>
                            @enderror
                            <div class="tool-actions">
                                <button type="submit" class="tool-btn primary" data-loading-button data-loading-text="Queueing..."><span class="tool-spinner" aria-hidden="true"></span><i class="fas fa-paper-plane" data-loading-icon></i> <span data-loading-label>Queue SMS</span></button>
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

<script>
document.querySelectorAll('[data-loading-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = document.activeElement?.matches('[data-loading-button]')
            ? document.activeElement
            : null;
        if (!button) return;

        const label = button.querySelector('[data-loading-label]');
        const icon = button.querySelector('[data-loading-icon]');
        button.disabled = true;
        button.classList.add('loading');
        if (icon) icon.style.display = 'none';
        if (label) label.textContent = button.dataset.loadingText || 'Working...';
    });
});

document.querySelectorAll('[data-poll-form]').forEach((form) => {
    const previewButton = form.querySelector('[data-preview-poll]');
    const preview = form.querySelector('[data-poll-preview]');
    const previewQuestion = form.querySelector('[data-preview-question]');
    const previewOptions = form.querySelector('[data-preview-options]');

    if (!previewButton || !preview || !previewQuestion || !previewOptions) return;

    previewButton.addEventListener('click', () => {
        const question = form.querySelector('[data-poll-question]').value.trim();
        const options = form.querySelector('[data-poll-options]').value
            .split(/\r\n|\r|\n/)
            .map((option) => option.trim())
            .filter(Boolean);

        previewQuestion.textContent = question || 'Add a poll question to preview it here.';
        previewOptions.innerHTML = '';

        if (options.length === 0) {
            const item = document.createElement('li');
            item.textContent = 'Add at least two options, one per line.';
            previewOptions.appendChild(item);
        } else {
            options.forEach((option) => {
                const item = document.createElement('li');
                item.textContent = option;
                previewOptions.appendChild(item);
            });
        }

        preview.classList.add('is-visible');
    });
});
</script>

@endsection





