@extends('layouts.landing')

@section('title', 'Tokens - Aspirant Dashboard')

@section('content')
<style>
body { background:#090909; color:#f5f5f0; }
.token-wrap { max-width:1440px; margin:0 auto; padding:26px 32px 80px; display:grid; grid-template-columns:280px minmax(0,1fr); gap:22px; align-items:start; }
.token-content { min-width:0; }
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
.token-top { display:flex; justify-content:space-between; gap:18px; align-items:flex-start; margin-bottom:24px; }
.token-title { margin:0; font-family:'Oswald',sans-serif; font-size:38px; color:#fff; }
.token-sub { margin:8px 0 0; color:rgba(245,245,240,.62); }
.token-btn { display:inline-flex; align-items:center; gap:8px; border-radius:8px; border:1px solid rgba(255,255,255,.12); padding:11px 14px; color:#fff; text-decoration:none; font-weight:800; background:#141414; }
.token-btn.primary { background:#006600; border-color:#00A86B; }
.token-grid { display:grid; grid-template-columns:1.35fr .65fr; gap:18px; }
.token-panel { border:1px solid rgba(255,255,255,.08); border-radius:8px; background:#121212; padding:20px; }
.token-balance { font-size:48px; font-weight:900; color:#4ade80; line-height:1; }
.package-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-top:18px; }
.package-card { border:1px solid rgba(255,255,255,.09); border-radius:8px; background:#0d0d0d; padding:16px; }
.package-card h3 { margin:0; color:#fff; font-size:20px; }
.package-card strong { display:block; margin:8px 0; color:#4ade80; font-size:28px; }
.token-form { display:grid; gap:10px; margin-top:14px; }
.token-form input,.token-form select,.token-form textarea { width:100%; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:#080808; color:#fff; padding:11px; }
.token-table { width:100%; border-collapse:collapse; }
.token-table th,.token-table td { padding:10px; border-top:1px solid rgba(255,255,255,.07); text-align:left; font-size:13px; }
.token-table th { color:rgba(245,245,240,.48); text-transform:uppercase; font-size:11px; }
.token-alert { margin-bottom:14px; border:1px solid rgba(34,197,94,.3); border-radius:8px; background:rgba(34,197,94,.12); color:#bbf7d0; padding:14px; }
@media (max-width:1100px) { .token-wrap { grid-template-columns:1fr; } .asp-sidebar { position:static; max-height:none; } .asp-sidebar-nav { display:flex; overflow-x:auto; padding-bottom:4px; } .asp-sidebar-link { flex:0 0 auto; } .asp-sidebar-footer { margin-top:12px; } }
@media (max-width:900px) { .token-grid,.package-grid { grid-template-columns:1fr; } .token-top { flex-direction:column; } }
@media (max-width:760px) { .token-wrap { padding:22px 16px 64px; } }
</style>
<div class="flag-stripe"></div>
@include('components.frontend-nav')
<main class="token-wrap">
    @include('components.aspirant-sidebar')

    <div class="token-content">
    <div class="token-top">
        <div>
            <h1 class="token-title">Campaign Tokens</h1>
            <p class="token-sub">{{ $candidate->name }} has {{ number_format($wallet->balance) }} tokens available.</p>
        </div>
        <a href="{{ route('aspirant.dashboard') }}" class="token-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
    </div>
    @if(session('success'))<div class="token-alert">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="token-alert" style="border-color:rgba(245,158,11,.3);background:rgba(245,158,11,.12);color:#fde68a;">{{ session('warning') }}</div>@endif
    <div class="token-grid">
        <section class="token-panel">
            <span class="token-sub">Current balance</span>
            <div class="token-balance">{{ number_format($wallet->balance) }}</div>
            <div class="package-grid">
                @foreach($packages as $package)
                    <article class="package-card">
                        <h3>{{ $package->name }}</h3>
                        <strong>{{ number_format($package->token_amount) }}</strong>
                        <p class="token-sub">{{ $package->currency }} {{ number_format($package->price) }}</p>
                        <p class="token-sub">{{ $package->description }}</p>
                        <form class="token-form" method="POST" action="{{ route('aspirant.tokens.purchase') }}">
                            @csrf
                            <input type="hidden" name="candidate_token_package_id" value="{{ $package->id }}">
                            <select name="payment_method_id">
                                <option value="">Payment method</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="payment_reference" placeholder="Payment reference" required>
                            <button class="token-btn primary" type="submit"><i class="fas fa-coins"></i> Credit Tokens</button>
                        </form>
                    </article>
                @endforeach
            </div>
        </section>
        <aside class="token-panel">
            <h2>Current Rates</h2>
            <table class="token-table">
                <tbody>
                    @foreach($rates as $rate)
                        <tr><td>{{ $rate->label }}</td><td>{{ $rate->token_amount }} {{ str_replace('_', ' ', $rate->calculation_type) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </aside>
    </div>
    <section class="token-panel" style="margin-top:18px;">
        <h2>Recent Ledger</h2>
        <table class="token-table">
            <thead><tr><th>Date</th><th>Type</th><th>Action</th><th>Amount</th><th>Balance</th></tr></thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr><td>{{ $transaction->created_at->format('M j, H:i') }}</td><td>{{ ucfirst($transaction->type) }}</td><td>{{ $transaction->action_label ?: '-' }}</td><td>{{ number_format($transaction->amount) }}</td><td>{{ number_format($transaction->balance_after) }}</td></tr>
                @empty
                    <tr><td colspan="5">No token transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
    </div>
</main>
@endsection

