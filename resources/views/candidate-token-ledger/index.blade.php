@extends('layouts.app')
@section('page_title', 'Token Ledger')
@section('content')
<style>
.ledger-shell{max-width:1440px;margin:0 auto}.ledger-head{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin-bottom:28px}.ledger-title{color:#fff;font-size:34px;font-weight:750;line-height:1.15}.ledger-title i{color:#10b981;margin-right:10px}.ledger-subtitle{color:#a1a1aa;margin-top:8px}.ledger-count{padding:9px 14px;border:1px solid #2b2b30;border-radius:999px;background:#18181b;color:#d4d4d8;font-weight:700;white-space:nowrap}.ledger-filter{display:grid;grid-template-columns:minmax(240px,1fr) 220px auto auto;gap:12px;padding:18px;margin-bottom:22px;border:1px solid #29292e;border-radius:20px;background:#141416}.ledger-control{width:100%;min-height:48px;padding:0 16px;border:1px solid #3f3f46;border-radius:12px;background:#202024;color:#fff}.ledger-button{min-height:48px;padding:0 22px;border:0;border-radius:12px;background:#059669;color:#fff;font-weight:800}.ledger-clear{display:flex;align-items:center;justify-content:center;min-height:48px;padding:0 18px;border:1px solid #3f3f46;border-radius:12px;color:#d4d4d8}.ledger-card{overflow:hidden;border:1px solid #29292e;border-radius:24px;background:#17171a}.ledger-table-wrap{overflow-x:auto}.ledger-table{width:100%;min-width:1080px;border-collapse:collapse}.ledger-table th{padding:18px 20px;background:#0b0b0d;color:#a1a1aa;font-size:12px;letter-spacing:.09em;text-align:left;text-transform:uppercase;white-space:nowrap}.ledger-table td{padding:18px 20px;border-top:1px solid #29292e;color:#e4e4e7;vertical-align:middle}.ledger-table tr:hover td{background:#1d1d21}.ledger-candidate{font-weight:800;color:#fff}.ledger-action{max-width:300px;color:#d4d4d8}.ledger-badge{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;background:#27272a;color:#d4d4d8;font-size:12px;font-weight:800;text-transform:capitalize}.ledger-badge.credit,.ledger-badge.purchase,.ledger-badge.grant,.ledger-badge.refund{background:rgba(16,185,129,.12);color:#34d399}.ledger-badge.debit,.ledger-badge.usage,.ledger-badge.spend{background:rgba(239,68,68,.12);color:#f87171}.ledger-amount{font-size:17px;font-weight:900;white-space:nowrap}.ledger-amount.in{color:#34d399}.ledger-amount.out{color:#f87171}.ledger-flow{display:flex;align-items:center;gap:9px;white-space:nowrap}.ledger-flow span{padding:6px 9px;border-radius:9px;background:#242428;font-weight:700}.ledger-flow i{color:#71717a;font-size:11px}.ledger-date{white-space:nowrap}.ledger-empty{padding:70px 24px!important;text-align:center;color:#71717a!important}.ledger-pagination{margin-top:24px}@media(max-width:900px){.ledger-head{align-items:flex-start;flex-direction:column}.ledger-filter{grid-template-columns:1fr 1fr}.ledger-title{font-size:29px}}@media(max-width:600px){.ledger-filter{grid-template-columns:1fr}.ledger-button,.ledger-clear{width:100%}}
</style>
<div class="ledger-shell">
    <header class="ledger-head">
        <div><h1 class="ledger-title"><i class="fas fa-list-ul"></i>Token Ledger</h1><p class="ledger-subtitle">Immutable token credits, grants, purchases and usage across aspirant wallets.</p></div>
        <span class="ledger-count">{{ number_format($transactions->total()) }} entries</span>
    </header>
    <form method="GET" action="{{ route('candidate-token-ledger.index') }}" class="ledger-filter">
        <input class="ledger-control" name="search" value="{{ request('search') }}" maxlength="120" placeholder="Search aspirant or activity">
        <select class="ledger-control" name="type"><option value="">All transaction types</option>@foreach(['purchase','grant','credit','debit','usage','refund'] as $type)<option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>@endforeach</select>
        <button class="ledger-button"><i class="fas fa-search"></i>&nbsp; Filter</button>
        <a class="ledger-clear" href="{{ route('candidate-token-ledger.index') }}">Clear</a>
    </form>
    <section class="ledger-card">
        <div class="ledger-table-wrap"><table class="ledger-table"><thead><tr><th>Aspirant</th><th>Transaction</th><th>Activity</th><th>Amount</th><th>Balance movement</th><th>Date</th></tr></thead><tbody>
        @forelse($transactions as $tx)
            @php($direction = $tx->amount >= 0 ? 'in' : 'out')
            <tr><td><span class="ledger-candidate">{{ $tx->candidate->name ?? 'Unknown aspirant' }}</span></td><td><span class="ledger-badge {{ strtolower($tx->type) }}">{{ $tx->type }}</span></td><td class="ledger-action">{{ $tx->action_label ?: \Illuminate\Support\Str::headline($tx->action_key ?: 'Token activity') }}</td><td><span class="ledger-amount {{ $direction }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount) }}</span></td><td><div class="ledger-flow"><span>{{ number_format($tx->balance_before) }}</span><i class="fas fa-arrow-right"></i><span>{{ number_format($tx->balance_after) }}</span></div></td><td class="ledger-date">{{ $tx->created_at->format('d M Y') }}<br><small class="text-zinc-500">{{ $tx->created_at->format('H:i') }}</small></td></tr>
        @empty<tr><td colspan="6" class="ledger-empty"><i class="fas fa-receipt text-3xl mb-3"></i><br>No ledger entries match these filters.</td></tr>@endforelse
        </tbody></table></div>
    </section>
    <div class="ledger-pagination">{{ $transactions->links() }}</div>
</div>
@endsection
