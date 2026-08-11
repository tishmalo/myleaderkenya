@extends('layouts.landing')
@section('title', 'My Toolbox - My Leader Kenya')
@push('styles')
@include('account.partials.news-styles')
<style>
.toolbox-balance{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:24px;border:1px solid rgba(16,185,129,.3);border-radius:22px;background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(9,9,11,.9))}.toolbox-balance strong{display:block;font:700 42px 'Oswald',sans-serif;color:#6ee7b7}.toolbox-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:18px}.toolbox-card{border:1px solid #29292d;border-radius:20px;background:#151517;padding:20px}.toolbox-card h2,.toolbox-card h3{color:#fff}.toolbox-muted{color:#a1a1aa}.toolbox-section{margin-top:34px}.toolbox-form{display:grid;gap:10px;margin-top:15px}.toolbox-input{width:100%;border:1px solid #3f3f46;border-radius:12px;background:#242427;color:#fff;padding:11px 13px}.toolbox-button{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:12px;background:#059669;padding:12px 17px;color:#fff;font-weight:800;cursor:pointer}.toolbox-button:disabled{background:#3f3f46;color:#a1a1aa;cursor:not-allowed}.toolbox-list{display:grid;gap:12px;margin-top:16px}.toolbox-row{display:flex;align-items:center;justify-content:space-between;gap:18px;border:1px solid #29292d;border-radius:16px;background:#151517;padding:17px}.toolbox-tags{display:flex;flex-wrap:wrap;gap:7px;margin-top:9px}.toolbox-tag,.toolbox-status{border-radius:999px;background:rgba(16,185,129,.12);padding:5px 9px;color:#6ee7b7;font-size:12px;font-weight:800}.toolbox-status.awaiting_payment{background:rgba(245,158,11,.13);color:#fbbf24}.toolbox-status.refunded{background:rgba(96,165,250,.13);color:#93c5fd}.toolbox-status.failed{background:rgba(239,68,68,.13);color:#fca5a5}.toolbox-alert{margin-top:15px;border-radius:14px;padding:13px 16px}.toolbox-alert.success{background:rgba(16,185,129,.13);color:#a7f3d0}.toolbox-alert.warning,.toolbox-alert.error{background:rgba(245,158,11,.13);color:#fde68a}.toolbox-amount.in{color:#6ee7b7}.toolbox-amount.out{color:#fca5a5}.toolbox-tabs{display:flex;gap:8px;margin-top:28px;padding:7px;border:1px solid #29292d;border-radius:16px;background:#111113;overflow-x:auto}.toolbox-tab{display:inline-flex;flex:1;min-width:max-content;align-items:center;justify-content:center;gap:8px;border:0;border-radius:11px;background:transparent;padding:12px 16px;color:#a1a1aa;font-weight:800;cursor:pointer}.toolbox-tab:hover{color:#fff;background:#202023}.toolbox-tab[aria-selected="true"]{color:#a7f3d0;background:rgba(16,185,129,.15);box-shadow:inset 0 0 0 1px rgba(16,185,129,.3)}.toolbox-tab-count{display:inline-grid;min-width:22px;height:22px;place-items:center;border-radius:999px;background:#29292d;padding:0 6px;font-size:11px}.toolbox-tab[aria-selected="true"] .toolbox-tab-count{background:#065f46;color:#d1fae5}.toolbox-tab-panel[hidden],.toolbox-objective-panel[hidden]{display:none!important}.toolbox-objective-panel{display:grid;gap:10px;border:1px solid rgba(16,185,129,.2);border-radius:13px;background:rgba(16,185,129,.05);padding:12px}.toolbox-message{min-height:90px;resize:vertical}.toolbox-reply{margin-top:10px;border-radius:10px;background:rgba(16,185,129,.09);padding:10px;color:#a7f3d0}@media(max-width:900px){.toolbox-grid{grid-template-columns:1fr}.toolbox-row{align-items:flex-start;flex-direction:column}}@media(max-width:700px){.toolbox-tabs{justify-content:flex-start}.toolbox-tab{flex:0 0 auto}.toolbox-tab span:not(.toolbox-tab-count){display:none}}
</style>
@endpush
@section('content')
<div class="flag-stripe"></div>@include('components.frontend-nav')
<main class="news-account-shell"><div class="news-account-layout">
@include('components.my-account-sidebar')
<section class="news-account-content">
<p class="news-kicker">Donor Toolbox</p><h1 class="news-title">Sponsor campaign tools</h1><p class="news-subtitle">Buy tokens once, fund several aspirants, and track every payment or refund from your personal wallet.</p>
@if(session('success'))<div class="toolbox-alert success">{{ session('success') }}</div>@endif
@if(session('warning'))<div class="toolbox-alert warning">{{ session('warning') }}</div>@endif
@if($errors->any())<div class="toolbox-alert error">{{ $errors->first() }}</div>@endif
<div class="toolbox-balance toolbox-section"><div><span class="toolbox-muted">Available balance</span><strong>{{ number_format($wallet->balance) }} tokens</strong></div><i class="fas fa-toolbox text-4xl text-emerald-400"></i></div>
@php($defaultToolboxTab = request('tab') === 'direct-support' ? 'direct-support' : ($adoptions->isNotEmpty() ? 'sponsorships' : 'topup'))
<nav class="toolbox-tabs" role="tablist" aria-label="Toolbox sections" data-toolbox-tabs data-default-tab="{{ $defaultToolboxTab }}">
    <button class="toolbox-tab" type="button" role="tab" data-toolbox-tab="topup" aria-controls="toolbox-panel-topup"><i class="fas fa-coins"></i><span>Top Up</span></button>
    <button class="toolbox-tab" type="button" role="tab" data-toolbox-tab="sponsorships" aria-controls="toolbox-panel-sponsorships"><i class="fas fa-hand-holding-heart"></i><span>Sponsorships</span><span class="toolbox-tab-count">{{ $adoptions->count() }}</span></button>
    <button class="toolbox-tab" type="button" role="tab" data-toolbox-tab="direct-support" aria-controls="toolbox-panel-direct-support"><i class="fas fa-heart"></i><span>Aspirant Support</span><span class="toolbox-tab-count">{{ $directSupports->count() }}</span></button>
    <button class="toolbox-tab" type="button" role="tab" data-toolbox-tab="activity" aria-controls="toolbox-panel-activity"><i class="fas fa-receipt"></i><span>Token Activity</span><span class="toolbox-tab-count">{{ $transactions->count() }}</span></button>
    <button class="toolbox-tab" type="button" role="tab" data-toolbox-tab="purchases" aria-controls="toolbox-panel-purchases"><i class="fas fa-wallet"></i><span>Purchases</span><span class="toolbox-tab-count">{{ $purchases->count() }}</span></button>
</nav>
<section class="toolbox-section toolbox-tab-panel" id="toolbox-panel-topup" role="tabpanel" data-toolbox-panel="topup" @if($defaultToolboxTab !== 'topup') hidden @endif><h2 class="news-title" style="font-size:30px">Top up your Toolbox</h2><p class="toolbox-muted">Purchased tokens stay in your personal wallet until you choose a sponsorship.</p><div class="toolbox-grid">
@forelse($packages as $package)<article class="toolbox-card"><h3 class="text-xl font-bold">{{ $package->name }}</h3><p class="mt-2 text-3xl font-bold text-emerald-300">{{ number_format($package->token_amount) }} tokens</p><p class="toolbox-muted">{{ $package->currency }} {{ number_format($package->price, 2) }}</p><form method="POST" action="{{ route('account.toolbox.purchase') }}" class="toolbox-form" data-toolbox-objective-form>@csrf<input type="hidden" name="candidate_token_package_id" value="{{ $package->id }}"><input class="toolbox-input" name="name" value="{{ old('name', auth()->user()->name) }}" placeholder="Your name" required><input class="toolbox-input" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" placeholder="Receipt email" required><select class="toolbox-input" name="objective" required data-toolbox-objective><option value="my_kitty">My Kitty</option><option value="support_aspirant">Support An Aspirant</option></select><div class="toolbox-objective-panel" data-kitty-panel><label class="toolbox-muted text-sm">Select type of kitty</label><select class="toolbox-input" name="kitty_type_id" required><option value="">Select kitty type</option>@foreach($kittyTypes as $kittyType)<option value="{{ $kittyType->id }}">{{ $kittyType->name }}</option>@endforeach</select></div><div class="toolbox-objective-panel" data-support-panel hidden><x-aspirant-search name="candidate_id" :search-url="route('aspirants.search')" label="Aspirant name to support" placeholder="Type the aspirant's name..." help="Select the approved aspirant who should receive this support." /><textarea class="toolbox-input toolbox-message" name="message" maxlength="1000" placeholder="Message to the Aspirant"></textarea><p class="toolbox-muted text-sm">KES {{ number_format($package->price, 2) }} will be paid directly. The platform retains 20%; the aspirant receives KES {{ number_format($package->price * 0.8, 2) }}. No tokens are added to your wallet.</p></div><input class="toolbox-input" name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="Payment phone" required><button class="toolbox-button"><i class="fas fa-wallet mr-2"></i> Continue to payment</button></form></article>
@empty<div class="toolbox-card toolbox-muted">No token packages are available right now.</div>@endforelse
</div></section>
<section class="toolbox-section toolbox-tab-panel" id="toolbox-panel-direct-support" role="tabpanel" data-toolbox-panel="direct-support" @if($defaultToolboxTab !== 'direct-support') hidden @endif><h2 class="news-title" style="font-size:30px">Support sent to aspirants</h2><p class="toolbox-muted">These payments go directly to the selected aspirant and never enter your token balance.</p><div class="toolbox-list">
@forelse($directSupports as $support)<article class="toolbox-row"><div><h3 class="font-bold text-white">{{ $support->candidate->name ?? 'Aspirant' }}</h3><p class="toolbox-muted">{{ $support->currency }} {{ number_format($support->gross_amount, 2) }} &middot; Aspirant receives {{ number_format($support->aspirant_amount, 2) }} after {{ number_format($support->platform_fee_rate, 0) }}% platform fee</p><p class="mt-2 text-white">{{ $support->message }}</p>@if($support->aspirant_reply)<div class="toolbox-reply"><strong>Reply from the aspirant</strong><br>{{ $support->aspirant_reply }}</div>@endif</div><span class="toolbox-status {{ $support->status }}">{{ ucfirst($support->status) }}</span></article>@empty<div class="toolbox-card toolbox-muted">You have not sent direct support to an aspirant yet.</div>@endforelse
</div></section>
<section class="toolbox-section toolbox-tab-panel" id="toolbox-panel-sponsorships" role="tabpanel" data-toolbox-panel="sponsorships" @if($defaultToolboxTab !== 'sponsorships') hidden @endif><h2 class="news-title" style="font-size:30px">My sponsorship requests</h2><div class="toolbox-list">
@forelse($adoptions as $adoption)<article class="toolbox-row"><div><h3 class="text-xl font-bold text-white">{{ $adoption->candidate->name ?? 'Aspirant' }}</h3><div class="toolbox-tags"><span class="toolbox-tag">{{ $adoption->campaignTool->title ?? $adoption->tool_title }}</span>@if($adoption->package)<span class="toolbox-tag">{{ $adoption->package->name }}</span>@endif</div>
@if($adoption->fulfilment_type === 'sms_sponsorship' && in_array($adoption->payment_status,['paid','refunded','partially_refunded'],true))<p class="toolbox-muted mt-2">SMS sponsorship: <strong class="text-white">{{ number_format($adoption->tokens_required) }} tokens</strong></p>@endif
@if($adoption->fulfilment_type === 'paid_package' && $adoption->package)<p class="toolbox-muted mt-2">Selected package: <strong class="text-white">{{ $adoption->package->name }} ({{ number_format($adoption->package->token_cost) }} tokens)</strong></p>@endif</div>
<div class="text-right"><span class="toolbox-status {{ $adoption->payment?->status ?? $adoption->payment_status }}">{{ str_replace('_',' ',ucfirst($adoption->payment?->status ?? $adoption->payment_status)) }}</span>
@if($adoption->fulfilment_type === 'sms_sponsorship' && $adoption->payment_status === 'awaiting_payment' && $adoption->status !== 'cancelled')<form method="POST" action="{{ route('account.toolbox.adoptions.pay', $adoption) }}" class="toolbox-form mt-3">@csrf<label class="toolbox-muted text-sm" for="sponsorship-{{ $adoption->id }}">Bulk SMS tokens to sponsor</label><input class="toolbox-input" id="sponsorship-{{ $adoption->id }}" type="number" name="token_amount" min="1" max="{{ $wallet->balance }}" placeholder="Enter tokens" required><button class="toolbox-button" {{ $wallet->balance < 1 ? 'disabled' : '' }}>Sponsor SMS tokens</button></form>@endif
@if($adoption->fulfilment_type === 'paid_package' && !in_array($adoption->payment?->status,['funded','fulfilled'],true) && $adoption->status !== 'cancelled')
    @if($adoption->campaignTool?->packages?->isNotEmpty())
    <form method="POST" action="{{ route('account.toolbox.tools.redeem', $adoption) }}" class="toolbox-form mt-3">
        @csrf
        <label class="toolbox-muted text-sm" for="package-{{ $adoption->id }}">Choose package and price</label>
        <select class="toolbox-input" id="package-{{ $adoption->id }}" name="package_id" required>
            <option value="">Select a package</option>
            @foreach($adoption->campaignTool->packages as $availablePackage)
                <option value="{{ $availablePackage->id }}" @selected((string)old('package_id') === (string)$availablePackage->id)>{{ $availablePackage->name }} - {{ number_format($availablePackage->token_cost) }} tokens</option>
            @endforeach
        </select>
        <button class="toolbox-button">Fund selected package</button>
    </form>
    @else
        <p class="toolbox-muted mt-2">No active packages are available for this tool yet.</p>
    @endif
@elseif($adoption->payment?->status === 'funded')<p class="toolbox-muted mt-2">Funded - awaiting admin setup.</p>@elseif($adoption->payment?->status === 'fulfilled')<p class="toolbox-muted mt-2">Activated for the aspirant campaign team.</p>@endif</div></article>
@empty<div class="toolbox-card toolbox-muted">You have no sponsorship requests yet. Start with &quot;Adopt An Aspirant&quot;.</div>@endforelse
</div></section>
<section class="toolbox-section toolbox-tab-panel" id="toolbox-panel-activity" role="tabpanel" data-toolbox-panel="activity" hidden><h2 class="news-title" style="font-size:30px">Token activity</h2><div class="toolbox-list">@forelse($transactions as $transaction)<article class="toolbox-row"><div><h3 class="font-bold text-white">{{ $transaction->action_label }}</h3><p class="toolbox-muted">{{ $transaction->candidate->name ?? 'Personal Toolbox' }} - {{ $transaction->created_at->format('d M Y, H:i') }}</p></div><strong class="toolbox-amount {{ $transaction->amount >= 0 ? 'in' : 'out' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount) }} tokens</strong></article>@empty<div class="toolbox-card toolbox-muted">No Toolbox transactions yet.</div>@endforelse</div></section>
<section class="toolbox-section toolbox-tab-panel" id="toolbox-panel-purchases" role="tabpanel" data-toolbox-panel="purchases" hidden><h2 class="news-title" style="font-size:30px">Purchase history</h2><div class="toolbox-list">@forelse($purchases as $purchase)<article class="toolbox-row"><div><h3 class="font-bold text-white">{{ $purchase->package_name }}</h3><p class="toolbox-muted">{{ $purchase->currency }} {{ number_format($purchase->price,2) }} - {{ $purchase->created_at->format('d M Y, H:i') }}@if($purchase->kitty_type) &middot; {{ \Illuminate\Support\Str::headline($purchase->kitty_type) }}@endif</p></div><span class="toolbox-status {{ $purchase->status }}">{{ ucfirst($purchase->status) }}</span></article>@empty<div class="toolbox-card toolbox-muted">No token purchases yet.</div>@endforelse</div></section>
</section></div></main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-toolbox-objective-form]').forEach(form => {
        const objective = form.querySelector('[data-toolbox-objective]');
        const kittyPanel = form.querySelector('[data-kitty-panel]');
        const supportPanel = form.querySelector('[data-support-panel]');
        const syncObjective = () => {
            const supporting = objective.value === 'support_aspirant';
            kittyPanel.hidden = supporting;
            supportPanel.hidden = !supporting;
            kittyPanel.querySelectorAll('select,input,textarea').forEach(field => field.disabled = supporting);
            supportPanel.querySelectorAll('select,input,textarea').forEach(field => field.disabled = !supporting);
            const message = supportPanel.querySelector('[name="message"]');
            if (message) message.required = supporting;
        };
        objective.addEventListener('change', syncObjective);
        syncObjective();
    });

    const tabsRoot = document.querySelector('[data-toolbox-tabs]');
    if (!tabsRoot) return;

    const tabs = Array.from(tabsRoot.querySelectorAll('[data-toolbox-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-toolbox-panel]'));
    const validTabs = tabs.map(tab => tab.dataset.toolboxTab);

    function activate(name, updateHash) {
        if (!validTabs.includes(name)) name = tabsRoot.dataset.defaultTab || validTabs[0];
        tabs.forEach(tab => {
            const active = tab.dataset.toolboxTab === name;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.tabIndex = active ? 0 : -1;
        });
        panels.forEach(panel => panel.hidden = panel.dataset.toolboxPanel !== name);
        if (updateHash && window.location.hash !== '#' + name) history.pushState(null, '', '#' + name);
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(tab.dataset.toolboxTab, true));
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            event.preventDefault();
            const direction = event.key === 'ArrowRight' ? 1 : -1;
            const next = tabs[(index + direction + tabs.length) % tabs.length];
            next.focus();
            activate(next.dataset.toolboxTab, true);
        });
    });

    window.addEventListener('hashchange', () => activate(window.location.hash.slice(1), false));
    activate(window.location.hash.slice(1), false);
});
</script>
@endpush
