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
.call-log-form { display:grid; gap:8px; min-width:260px; }
.call-log-form .call-log-row { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.call-log-form input,.call-log-form select { width:100%; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:#0b0b0b; color:#fff; padding:8px 9px; font:inherit; }
.call-log-status { min-height:18px; color:rgba(245,245,240,.62); font-size:12px; line-height:1.4; }
.call-log-status.success { color:#86efac; }
.call-log-status.error { color:#fca5a5; }
.call-row-logged td { background:rgba(34,197,94,.04); }
.tool-pagination { margin-top:14px; }
.tool-table { width:100%; border-collapse:collapse; }
.tool-table th,.tool-table td { padding:12px 10px; border-top:1px solid rgba(255,255,255,.06); text-align:left; font-size:13px; }
.tool-table th { color:rgba(245,245,240,.48); font-size:11px; text-transform:uppercase; letter-spacing:.09em; }
.tool-table td { color:rgba(245,245,240,.74); }
.tool-empty { color:rgba(245,245,240,.5); line-height:1.6; }
.tool-balance-strip { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin:0 0 20px; }
.tool-balance-card { border:1px solid rgba(255,255,255,.08); border-radius:8px; background:#0d0d0d; padding:15px; }
.tool-balance-card span { display:block; color:rgba(245,245,240,.48); font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
.tool-balance-card strong { display:block; margin-top:6px; color:#fff; font-size:24px; line-height:1; }
.tool-balance-card a { color:#4ade80; text-decoration:none; }
.tool-balance-card small { display:block; margin-top:8px; color:rgba(245,245,240,.55); line-height:1.35; }
.token-summary { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; margin-bottom:18px; }
.token-summary div { border:1px solid rgba(255,255,255,.07); border-radius:8px; background:#0d0d0d; padding:12px; }
.token-summary span { display:block; color:rgba(245,245,240,.5); font-size:11px; text-transform:uppercase; font-weight:800; }
.token-summary strong { display:block; margin-top:4px; color:#fff; font-size:18px; }
.sms-cost-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; margin:14px 0; }
.sms-cost-grid div { border:1px solid rgba(0,168,107,.18); border-radius:8px; background:#0b0f0d; padding:12px; }
.sms-cost-grid span { display:block; color:rgba(245,245,240,.5); font-size:10px; text-transform:uppercase; font-weight:800; }
.sms-cost-grid strong { display:block; margin-top:4px; color:#fff; font-size:17px; }
@media (max-width:980px) { .tool-grid,.tool-stats,.tool-balance-strip,.sms-cost-grid { grid-template-columns:1fr; } .tool-top { flex-direction:column; } }
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

                <div class="tool-balance-strip" data-tool-balance-strip>
                    <div class="tool-balance-card">
                        <span>Remaining Tokens</span>
                        <strong>{{ number_format($tokenWallet?->balance ?? 0) }}</strong>
                        <small><a href="{{ route('aspirant.tokens.index') }}">Buy tokens</a> before running paid actions.</small>
                    </div>
                    <div class="tool-balance-card">
                        <span>This Tool Rate</span>
                        @php
                            $currentRate = $module['key'] === 'bulk-sms' ? $tokenRates->get('bulk-sms') : null;
                        @endphp
                        <strong>{{ $currentRate ? number_format($currentRate->token_amount) : '-' }}</strong>
                        <small>{{ $currentRate ? str_replace('_', ' ', $currentRate->calculation_type) : 'Configured by admin' }}</small>
                    </div>
                    @if($module['key'] === 'bulk-sms')
                        <div class="tool-balance-card">
                            <span>SMS Balance</span>
                            <strong>{{ $smsProviderBalance['formatted'] ?? 'Unavailable' }}</strong>
                            <small>
                                Pulled from the Bulk SMS provider account.
                                @if(! empty($smsProviderBalance['error']))
                                    {{ $smsProviderBalance['error'] }}
                                @elseif($smsBalanceRequest)
                                    Latest support request: {{ str_replace('_', ' ', ucfirst($smsBalanceRequest->status)) }}.
                                @endif
                            </small>
                        </div>
                    @else
                        <div class="tool-balance-card">
                            <span>Token Ledger</span>
                            <strong><a href="{{ route('aspirant.tokens.index') }}">View</a></strong>
                            <small>See purchases, debits, and remaining balance.</small>
                        </div>
                    @endif
                </div>

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
                                    @php
                                        $selectedType = old('website_type', $websiteRequest->website_type ?? 'standard');
                                    @endphp
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
                                <textarea name="message" rows="6" maxlength="918" required placeholder="Write a focused SMS update for voters in your bloc." data-sms-message>{{ old('message') }}</textarea>
                            </label>
                            <div class="sms-cost-grid" data-sms-cost data-recipient-count="{{ $voterCount ?? 0 }}" data-token-rate="{{ $tokenRates->get('bulk-sms')?->token_amount ?? 0 }}" data-token-balance="{{ $tokenWallet?->balance ?? 0 }}">
                                <div><span>Characters</span><strong data-sms-characters>0</strong></div>
                                <div><span>Encoding</span><strong data-sms-encoding>GSM-7</strong></div>
                                <div><span>Segments</span><strong data-sms-segments>0</strong></div>
                                <div><span>SMS Units</span><strong data-sms-units>0</strong></div>
                                <div><span>Required Tokens</span><strong data-sms-tokens>0</strong></div>
                                <div><span>After Send</span><strong data-sms-projected>{{ number_format($tokenWallet?->balance ?? 0) }}</strong></div>
                            </div>
                            @error('message')
                                <div class="tool-alert">{{ $message }}</div>
                            @enderror
                            <div class="tool-actions">
                                <button type="submit" class="tool-btn primary" data-loading-button data-loading-text="Queueing..."><span class="tool-spinner" aria-hidden="true"></span><i class="fas fa-paper-plane" data-loading-icon></i> <span data-loading-label>Queue SMS</span></button><a href="{{ route('aspirant.tokens.index') }}" class="tool-btn"><i class="fas fa-coins"></i> Buy Tokens</a>
                            </div>
                        </form>

                        <div class="poll-card" style="margin-top:18px;">
                            <div class="poll-card-top"><h3>SMS Provider Balance Support</h3><span class="poll-status draft">Admin follow-up</span></div>
                            <p class="tool-note">SMS provider balance is separate from tokens. Send this to admin when the SMS account needs top-up or support.</p>
                            <form class="tool-form" method="POST" action="{{ route('aspirant.sms-balance-requests.store') }}">
                                @csrf
                                <label>Requested Amount
                                    <input type="number" name="requested_amount" min="1" placeholder="Optional amount">
                                </label>
                                <label>Notes
                                    <textarea name="message" rows="3" maxlength="1000" placeholder="Explain the SMS balance/top-up issue for admin."></textarea>
                                </label>
                                <button type="submit" class="tool-btn"><i class="fas fa-paper-plane"></i> Request Support</button>
                            </form>
                        </div>
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
                        @php
                            $savedPriority = old('callback_priority', $callScript->callback_priority ?? 'undecided');
                            $defaultScript = "Hello, this is the campaign team for {$candidate->name}. We are listening to voters in {$scope['label']} and would like to hear what matters most to you.";
                        @endphp
                        <form class="tool-form" method="POST" action="{{ route('aspirant.tools.call-center.script') }}" data-loading-form>
                            @csrf
                            <label>Call Script
                                <textarea name="script" rows="6" required maxlength="5000">{{ old('script', $callScript->script ?? $defaultScript) }}</textarea>
                            </label>
                            @error('script')
                                <div class="tool-alert">{{ $message }}</div>
                            @enderror
                            <label>Callback Priority
                                <select name="callback_priority" required>
                                    <option value="undecided" {{ $savedPriority === 'undecided' ? 'selected' : '' }}>Undecided voters</option>
                                    <option value="supporters" {{ $savedPriority === 'supporters' ? 'selected' : '' }}>Registered supporters</option>
                                    <option value="volunteers" {{ $savedPriority === 'volunteers' ? 'selected' : '' }}>Volunteer leads</option>
                                </select>
                            </label>
                            @error('callback_priority')
                                <div class="tool-alert">{{ $message }}</div>
                            @enderror
                            <div class="tool-actions">
                                <button type="submit" class="tool-btn primary" data-loading-button data-loading-text="Saving..."><span class="tool-spinner" aria-hidden="true"></span><i class="fas fa-save" data-loading-icon></i> <span data-loading-label>Save Script</span></button>
                                <a href="{{ route('aspirant.tools.show', ['key' => 'call-center', 'call_list' => 1]) }}" class="tool-btn"><i class="fas fa-phone"></i> Start Call List</a>
                            </div>
                        </form>

                        @if($callListActive)
                            <div class="poll-list">
                                <div class="poll-card">
                                    <div class="poll-card-top">
                                        <h3>Call List</h3>
                                        <span class="poll-status">{{ number_format(method_exists($callListContacts, 'total') ? $callListContacts->total() : $callListContacts->count()) }} ready</span>
                                    </div>
                                    @if($callScript)
                                        <p class="tool-note" style="margin-top:0;">Use the saved script above while calling voters in {{ $scope['label'] }}.</p>
                                    @else
                                        <div class="tool-alert" style="margin-bottom:12px;">Save the script first so the call team has the latest talking points.</div>
                                    @endif

                                    @if($callListContacts->isEmpty())
                                        <p class="tool-empty">No callable contacts with phone numbers were found in {{ $scope['label'] }}.</p>
                                    @else
                                        <div style="overflow-x:auto;">
                                            <table class="tool-table">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Phone</th>
                                                        <th>Ward</th>
                                                        <th>Log Call</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($callListContacts as $contact)
                                                        <tr data-call-row>
                                                            <td>{{ $contact->name ?: $contact->username }}</td>
                                                            <td>{{ $contact->phone }}</td>
                                                            <td>{{ $contact->ward ?: '-' }}</td>
                                                            <td>
                                                                <form class="call-log-form" method="POST" action="{{ route('aspirant.tools.call-center.calls') }}" data-call-log-form>
                                                                    @csrf
                                                                    <input type="hidden" name="voter_user_id" value="{{ $contact->id }}">
                                                                    <div class="call-log-row">
                                                                        <a class="tool-btn" style="padding:8px 10px; justify-content:center;" href="tel:{{ preg_replace('/\s+/', '', $contact->phone) }}" data-call-button><span class="tool-spinner" aria-hidden="true"></span><i class="fas fa-phone" data-call-icon></i> <span data-call-label>Call</span></a>
                                                                        <select name="outcome" required>
                                                                            <option value="reached">Reached</option>
                                                                            <option value="no_answer">No answer</option>
                                                                            <option value="busy">Busy</option>
                                                                            <option value="callback">Callback</option>
                                                                            <option value="supporter">Supporter</option>
                                                                            <option value="volunteer">Volunteer</option>
                                                                            <option value="not_interested">Not interested</option>
                                                                            <option value="wrong_number">Wrong number</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="call-log-row">
                                                                        <input type="datetime-local" name="callback_at" aria-label="Callback time">
                                                                        <input type="text" name="notes" maxlength="1000" placeholder="Notes">
                                                                    </div>
                                                                    <button type="submit" class="tool-btn primary" style="padding:8px 10px; justify-content:center;" data-call-log-button><span class="tool-spinner" aria-hidden="true"></span><i class="fas fa-check" data-call-log-icon></i> <span data-call-log-label>Log Call</span></button>
                                                                    <div class="call-log-status" data-call-log-status aria-live="polite"></div>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if(method_exists($callListContacts, 'links'))
                                            <div class="tool-pagination">{{ $callListContacts->links() }}</div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if($callLogs->isNotEmpty())
                            <div class="poll-list">
                                <div class="poll-card">
                                    <div class="poll-card-top">
                                        <h3>Recent Call Logs</h3>
                                        <span class="poll-status">{{ number_format($callLogs->count()) }} latest</span>
                                    </div>
                                    <div style="overflow-x:auto;">
                                        <table class="tool-table">
                                            <thead>
                                                <tr>
                                                    <th>Voter</th>
                                                    <th>Outcome</th>
                                                    <th>Callback</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($callLogs as $log)
                                                    <tr>
                                                        <td>{{ $log->voter_name ?: ($log->voter->name ?? $log->voter->username ?? '-') }}</td>
                                                        <td>{{ str_replace('_', ' ', ucfirst($log->outcome)) }}</td>
                                                        <td>{{ $log->callback_at ? $log->callback_at->format('M j, H:i') : '-' }}</td>
                                                        <td>{{ $log->notes ?: '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
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

document.querySelectorAll('[data-call-log-form]').forEach((form) => {
    const button = form.querySelector('[data-call-log-button]');
    const label = form.querySelector('[data-call-log-label]');
    const icon = form.querySelector('[data-call-log-icon]');
    const status = form.querySelector('[data-call-log-status]');
    const token = form.querySelector('input[name="_token"]')?.value || '';
    const row = form.closest('[data-call-row]');
    const callButton = form.querySelector('[data-call-button]');
    const callLabel = form.querySelector('[data-call-label]');
    const callIcon = form.querySelector('[data-call-icon]');

    const setStatus = (message, type = '') => {
        if (!status) return;
        status.textContent = message;
        status.classList.remove('success', 'error');
        if (type) status.classList.add(type);
    };

    const setLoading = (loading) => {
        if (!button) return;
        button.disabled = loading;
        button.classList.toggle('loading', loading);
        if (icon) icon.style.display = loading ? 'none' : '';
        if (label) label.textContent = loading ? 'Logging...' : 'Log Call';
    };
    const acknowledgeCallOpened = () => {
        if (!callButton) return;
        callButton.classList.add('loading');
        if (callIcon) callIcon.style.display = 'none';
        if (callLabel) callLabel.textContent = 'Calling...';
        setStatus('Opening phone dialer...');

        window.setTimeout(() => {
            callButton.classList.remove('loading');
            if (callIcon) callIcon.style.display = '';
            if (callLabel) callLabel.textContent = 'Call opened';
            setStatus('Call opened. Log the outcome here when done.', 'success');
        }, 900);
    };

    callButton?.addEventListener('click', acknowledgeCallOpened);


    const submitWithRetry = async (attempt = 1) => {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
            body: new FormData(form),
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (response.status >= 500 && attempt < 3) {
                return submitWithRetry(attempt + 1);
            }

            const validationMessage = payload.errors
                ? Object.values(payload.errors).flat().join(' ')
                : null;
            throw new Error(validationMessage || payload.message || 'Call log could not be saved.');
        }

        return payload;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setLoading(true);
        setStatus('Saving call log...');

        try {
            const payload = await submitWithRetry();
            setStatus(payload.message || 'Call log recorded.', 'success');
            row?.classList.add('call-row-logged');
            if (label) label.textContent = 'Logged';
            form.querySelector('input[name="notes"]')?.value = '';
            form.querySelector('input[name="callback_at"]')?.value = '';
        } catch (error) {
            setStatus(`${error.message} You can retry.`, 'error');
        } finally {
            setLoading(false);
            if (row?.classList.contains('call-row-logged') && label) {
                label.textContent = 'Logged';
            }
        }
    });
});

function smsDetails(message) {
    const basic = "@£$•ËÈ˘ÏÚ«\nÿ¯\r≈Â?_FG?O??ST? !\"#§%&'()*+,-./0123456789:;<=>?°ABCDEFGHIJKLMNOPQRSTUVWXYZƒ÷—‹`øabcdefghijklmnopqrstuvwxyz‰ˆÒ¸‡";
    const extended = "^{}\\[~]|Ä";
    let gsm = true;
    let length = 0;
    Array.from(message).forEach((character) => {
        if (extended.includes(character)) length += 2;
        else if (basic.includes(character)) length += 1;
        else { gsm = false; length += 1; }
    });
    const encoding = gsm ? 'GSM-7' : 'Unicode';
    const count = gsm ? length : Array.from(message).length;
    const segments = count === 0 ? 0 : (gsm ? (count <= 160 ? 1 : Math.ceil(count / 153)) : (count <= 70 ? 1 : Math.ceil(count / 67)));
    return { count, encoding, segments };
}

document.querySelectorAll('[data-sms-cost]').forEach((panel) => {
    const textarea = document.querySelector('[data-sms-message]');
    if (!textarea) return;
    const recipients = Number(panel.dataset.recipientCount || 0);
    const unitTokens = Number(panel.dataset.tokenRate || 1);
    const balance = Number(panel.dataset.tokenBalance || 0);
    const set = (selector, value) => { const node = panel.querySelector(selector); if (node) node.textContent = value; };
    const render = () => {
        const details = smsDetails(textarea.value || '');
        const units = recipients * details.segments;
        const tokens = units * unitTokens;
        set('[data-sms-characters]', details.count.toLocaleString());
        set('[data-sms-encoding]', details.encoding);
        set('[data-sms-segments]', details.segments.toLocaleString());
        set('[data-sms-units]', units.toLocaleString());
        set('[data-sms-tokens]', tokens.toLocaleString());
        set('[data-sms-projected]', Math.max(0, balance - tokens).toLocaleString());
    };
    textarea.addEventListener('input', render);
    render();
});
</script>

@endsection









