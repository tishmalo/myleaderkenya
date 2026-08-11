@extends('layouts.aspirant')
@section('title', 'Support Received')
@section('content')
<style>
.support-layout{display:flex;gap:28px;padding:28px;min-height:100vh;background:#080808;color:#f5f5f0}.support-main{min-width:0;flex:1}.support-kicker{color:#34d399;font-size:11px;font-weight:900;letter-spacing:.22em;text-transform:uppercase}.support-title{margin:7px 0 8px;font:700 38px 'Oswald',sans-serif}.support-muted{color:#a1a1aa}.support-total{margin:24px 0;padding:22px;border:1px solid rgba(16,185,129,.3);border-radius:18px;background:rgba(16,185,129,.09)}.support-total strong{display:block;margin-top:5px;color:#6ee7b7;font:700 38px 'Oswald',sans-serif}.support-list{display:grid;gap:14px}.support-card{border:1px solid #29292d;border-radius:18px;background:#151517;padding:20px}.support-head{display:flex;justify-content:space-between;gap:18px}.support-money{color:#6ee7b7;font-weight:900}.support-breakdown{margin-top:8px;color:#a1a1aa;font-size:13px}.support-note{margin-top:15px;border-left:3px solid #10b981;padding:10px 14px;background:#101412;color:#d4d4d8}.support-reply{margin-top:14px;padding:13px;border-radius:12px;background:rgba(16,185,129,.08);color:#a7f3d0}.support-form{display:grid;gap:9px;margin-top:14px}.support-form textarea{min-height:90px;border:1px solid #3f3f46;border-radius:12px;background:#242427;padding:12px;color:#fff}.support-form button{justify-self:start;border:0;border-radius:10px;background:#059669;padding:10px 15px;color:#fff;font-weight:800}.support-alert{margin-bottom:16px;border-radius:12px;background:rgba(16,185,129,.12);padding:12px;color:#a7f3d0}@media(max-width:900px){.support-layout{display:block;padding:16px}.support-main{margin-top:22px}.support-head{display:block}.support-money{display:block;margin-top:8px}}
</style>
<div class="support-layout">
    @include('components.aspirant-sidebar')
    <main class="support-main">
        <p class="support-kicker">{{ $candidate->name }}</p>
        <h1 class="support-title">Support Received</h1>
        <p class="support-muted">Direct financial support and messages from your supporters.</p>
        @if(session('success'))<div class="support-alert">{{ session('success') }}</div>@endif
        <div class="support-total"><span class="support-muted">Total available after platform fees</span><strong>KES {{ number_format($supportTotal, 2) }}</strong></div>
        <div class="support-list">
            @forelse($supports as $support)
                <article class="support-card">
                    <div class="support-head"><div><h2>{{ $support->supporter_name }}</h2><p class="support-muted">{{ $support->paid_at?->format('d M Y, H:i') }}</p></div><span class="support-money">KES {{ number_format($support->aspirant_amount, 2) }}</span></div>
                    <div class="support-breakdown">Gross: KES {{ number_format($support->gross_amount, 2) }} &middot; Platform fee ({{ number_format($support->platform_fee_rate, 0) }}%): KES {{ number_format($support->platform_fee_amount, 2) }}</div>
                    <div class="support-note">{{ $support->message }}</div>
                    @if($support->replied_at)
                        <div class="support-reply"><strong>Your reply</strong><br>{{ $support->aspirant_reply }}</div>
                    @else
                        <form method="POST" action="{{ route('aspirant.support.reply', $support) }}" class="support-form">@csrf<textarea name="reply" maxlength="1000" required placeholder="Write a thank-you reply..."></textarea><button type="submit">Send reply</button></form>
                    @endif
                </article>
            @empty
                <div class="support-card support-muted">No confirmed direct support payments yet.</div>
            @endforelse
        </div>
    </main>
</div>
@endsection
