@extends('layouts.landing')

@section('title', $poll['question'].' - My Leader Kenya Poll')
@section('meta_description', $poll['status_line'].' Cast your vote on My Leader Kenya.')
@section('og_image', $poll['share_og_image'])

@push('styles')
    @vite('resources/css/views/landing.css')

    <style>
    .poll-share-page {
        background: #101010;
        padding: 48px 0 80px;
    }
    .poll-share-inner {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 24px;
    }
    .poll-share-crumb {
        margin-bottom: 28px;
    }
    .poll-share-crumb-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: rgba(245,245,240,0.4);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        text-decoration: none;
        transition: color .2s;
    }
    .poll-share-crumb-link:hover { color: var(--green-bright); }
    .poll-share-head {
        margin-bottom: 30px;
    }
    .poll-share-title {
        font-family: 'Oswald', sans-serif;
        font-size: clamp(28px, 4vw, 44px);
        font-weight: 700;
        line-height: 1.08;
        letter-spacing: -0.5px;
        margin: 0 0 12px;
    }
    .poll-share-sub {
        color: rgba(245,245,240,0.45);
        font-size: 14px;
        margin: 0;
    }
    .poll-share-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 32px;
        padding: 18px 0;
        border-top: 1px solid rgba(255,255,255,0.07);
        border-bottom: 1px solid rgba(255,255,255,0.07);
    }
    .poll-share-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-right: 6px;
        color: rgba(245,245,240,0.6);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .poll-share-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        color: #fff;
        transition: filter .2s, transform .2s;
    }
    .poll-share-btn:hover {
        filter: brightness(1.12);
        transform: translateY(-1px);
    }
    .poll-share-btn.is-whatsapp  { background: #25D366; }
    .poll-share-btn.is-x         { background: #111; border: 1px solid rgba(255,255,255,0.18); }
    .poll-share-btn.is-facebook  { background: #1877F2; }
    .poll-share-btn.is-copy      { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); cursor: pointer; }

    @media (max-width: 768px) {
        .poll-share-bar { justify-content: flex-start; }
    }
    @media (max-width: 480px) {
        .poll-share-bar { flex-direction: column; align-items: stretch; }
        .poll-share-btn { justify-content: center; }
    }
    </style>
@endpush

@section('content')

<div class="flag-stripe"></div>
@include('components.frontend-nav')

<div class="poll-share-page">
    <div class="poll-share-inner">

        <nav class="poll-share-crumb" aria-label="Breadcrumb">
            <a href="{{ route('landing') }}" class="poll-share-crumb-link">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to all polls
            </a>
        </nav>

        <div class="poll-share-head">
            <div class="section-label">{{ $poll['section_label'] }}</div>
            <h1 class="poll-share-title">{{ $poll['question'] }}</h1>
            <p class="poll-share-sub">{{ $poll['status_line'] }}</p>
        </div>

        <section class="poll-section" id="poll">
            <div class="section-inner">
                @include('landing.partials.poll-options', ['poll' => $poll])
            </div>
        </section>

        <div class="poll-share-bar" aria-label="Share this poll">
            <span class="poll-share-label"><i class="fas fa-share-alt" aria-hidden="true"></i> Share this poll</span>
            <a class="poll-share-btn is-whatsapp"
               href="https://wa.me/?text={{ rawurlencode($poll['question'].' '.$shareUrl) }}"
               target="_blank" rel="noopener noreferrer">
                <i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp
            </a>
            <a class="poll-share-btn is-x"
               href="https://twitter.com/intent/tweet?text={{ rawurlencode($poll['question']) }}&url={{ rawurlencode($shareUrl) }}"
               target="_blank" rel="noopener noreferrer">
                <i class="fab fa-x-twitter" aria-hidden="true"></i> X
            </a>
            <a class="poll-share-btn is-facebook"
               href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}"
               target="_blank" rel="noopener noreferrer">
                <i class="fab fa-facebook-f" aria-hidden="true"></i> Facebook
            </a>
            <a class="poll-share-btn is-copy" href="#" data-poll-copy-link="{{ $shareUrl }}">
                <i class="fas fa-link" aria-hidden="true"></i> Copy link
            </a>
        </div>

    </div>
</div>

<script type="application/ld+json">
@php
    $pollSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $poll['question'],
        'description' => $poll['status_line'],
        'url' => $shareUrl,
        'datePublished' => $poll['share_created_at'],
        'author' => ['@type' => 'Organization', 'name' => 'My Leader Kenya'],
        'publisher' => ['@type' => 'Organization', 'name' => 'My Leader Kenya'],
    ];
@endphp
{{ json_encode($pollSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}
</script>

<footer>
    <div class="footer-logo">TUKO KADI</div>
    <p class="footer-copy">&copy; {{ date('Y') }} Tuko Kadi. All rights reserved.</p>
    <div class="footer-links">
        <a href="{{ route('privacy') }}">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Contact Us</a>
    </div>
</footer>

@endsection

@push('scripts')
    <script>
    document.querySelectorAll('[data-poll-copy-link]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var url = btn.getAttribute('data-poll-copy-link');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () {
                    var old = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i> Link copied';
                    window.setTimeout(function () { btn.innerHTML = old; }, 2000);
                });
            } else {
                window.prompt('Copy this poll link:', url);
            }
        });
    });
    </script>
@endpush