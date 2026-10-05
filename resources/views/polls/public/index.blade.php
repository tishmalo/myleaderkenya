@extends('layouts.landing')
@section('title', 'Polls - My Leader Kenya')
@section('meta_description', 'Vote in My Leader Kenya polls and see how your county and country are shaping up ahead of elections.')

@section('content')
<div class="flag-stripe"></div>
@include('components.frontend-nav')

<main class="polls-public-shell">
    <section class="polls-public-hero">
        <div class="section-inner">
            <div class="section-header">
                <div class="section-label">Polls</div>
                <h1 class="section-title">Have your say</h1>
                <p class="section-sub">Vote in live polls for your area and share them with your community.</p>
            </div>
        </div>
    </section>

    @forelse($polls as $poll)
        @include('landing.partials.poll', ['poll' => $poll])
    @empty
        <section class="polls-public-empty">
            <div class="section-inner">
                <div class="empty-card">
                    <h2 class="empty-title">No polls just yet</h2>
                    <p class="empty-text">Check back soon as we add more polls for your county, constituency and the country.</p>
                    <a class="btn-hero-primary" href="{{ route('landing') }}">
                        <i class="fas fa-home"></i> Back to home
                    </a>
                </div>
            </div>
        </section>
    @endforelse
</main>

<footer>
    <div class="footer-logo">TUKO KADI</div>
    <p class="footer-copy">&copy; {{ date('Y') }} Tuko Kadi. All rights reserved.</p>
    <div class="footer-links">
        <a href="{{ route('privacy') }}">Privacy Policy</a>
        <a href="{{ route('about.public') }}">About Us</a>
        <a href="#">Terms of Service</a>
        <a href="#">Contact Us</a>
    </div>
</footer>
<div class="flag-stripe"></div>
@endsection

@push('styles')
<style>
.polls-public-shell { background:#050505; color:#fff; }
.polls-public-hero { padding: clamp(40px,8vw,90px) 0 0; }
.polls-public-empty { padding: clamp(30px,6vw,60px) 0 clamp(60px,10vw,110px); }
.empty-card { border:1px solid rgba(255,255,255,0.08); border-radius:16px; background:radial-gradient(1200px 400px at 50% 0, rgba(5,150,105,0.18), transparent); padding: clamp(28px,6vw,56px); text-align:center; display:flex; flex-direction:column; align-items:center; gap:12px; }
.empty-title { margin:0; font-size:clamp(24px,4vw,36px); font-weight:800; }
.empty-text { margin:0; color:#a1a1aa; max-width:520px; }
</style>
@endpush
