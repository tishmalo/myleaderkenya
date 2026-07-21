@extends('layouts.landing')

@section('title', 'Tokens - Aspirant Dashboard')

@section('content')
<style>
body { background:#090909; color:#f5f5f0; }
.token-wrap { max-width:1180px; margin:0 auto; padding:38px 24px 80px; }
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
@media (max-width:900px) { .token-grid,.package-grid { grid-template-columns:1fr; } .token-top { flex-direction:column; } }
</style>
<div class="flag-stripe"></div>
@include('components.frontend-nav')
<main class="token-wrap">
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
</main>
@endsection
