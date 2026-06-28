@extends('layouts.landing')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap');

    :root {
        --kenya-green:  #006600;
        --kenya-red:    #BB0000;
        --kenya-black:  #0a0a0a;
        --kenya-white:  #F5F5F0;
        --green-bright: #00A86B;
        --red-bright:   #E8001A;
        --gold:         #D4AF37;
        --gold-dim:     #a88a20;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Barlow', sans-serif;
        background: var(--kenya-black);
        color: var(--kenya-white);
        overflow-x: hidden;
    }

    h1, h2, h3, h4 { font-family: 'Oswald', sans-serif; }

    /* ── FLAG STRIPE ── */
    .flag-stripe {
        height: 5px;
        background: linear-gradient(90deg,
            var(--kenya-black) 0% 33.3%,
            var(--kenya-red)   33.3% 66.6%,
            var(--kenya-green) 66.6% 100%
        );
    }

    /* ── PAGE WRAPPER ── */
    .pp-page {
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }

    /* Subtle background glow */
    .pp-page::before {
        content: '';
        position: fixed; inset: 0; pointer-events: none; z-index: 0;
        background:
            radial-gradient(ellipse 55% 50% at 95% 10%, rgba(187,0,0,0.07) 0%, transparent 60%),
            radial-gradient(ellipse 45% 40% at 5%  90%, rgba(0,102,0,0.07) 0%, transparent 60%),
            radial-gradient(ellipse 30% 25% at 50% 50%, rgba(212,175,55,0.03) 0%, transparent 60%);
    }

    /* ── NAV (minimal top bar matching landing) ── */
    .pp-nav {
        background: rgba(10,10,10,0.97);
        border-bottom: 1px solid rgba(255,255,255,0.06);
        backdrop-filter: blur(16px);
        position: sticky; top: 5px; z-index: 100;
    }
    .pp-nav-inner {
        max-width: 1100px; margin: 0 auto; padding: 18px 32px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .pp-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .pp-brand-logo {
        width: 38px; height: 38px; border-radius: 9px;
        background: var(--kenya-red);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Oswald', sans-serif; font-size: 14px; font-weight: 700; color: white;
        position: relative; overflow: hidden;
    }
    .pp-brand-logo::after {
        content: ''; position: absolute; top: 0; right: 0;
        width: 50%; height: 100%; background: var(--kenya-green);
    }
    .pp-brand-logo span { position: relative; z-index: 1; }
    .pp-brand-name {
        font-family: 'Oswald', sans-serif; font-size: 18px; font-weight: 700;
        color: white; letter-spacing: 1px;
    }
    .pp-back {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
        color: rgba(255,255,255,0.35); text-decoration: none;
        transition: color 0.2s;
    }
    .pp-back:hover { color: var(--green-bright); }

    /* ── HERO HEADER ── */
    .pp-hero {
        position: relative; z-index: 1;
        max-width: 1100px; margin: 0 auto; padding: 80px 32px 60px;
        display: grid; grid-template-columns: 1fr auto; align-items: end; gap: 40px;
    }
    .pp-eyebrow {
        display: inline-flex; align-items: center; gap: 10px;
        border: 1px solid rgba(212,175,55,0.35);
        background: rgba(212,175,55,0.07);
        padding: 7px 18px; border-radius: 40px;
        font-size: 10px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase;
        color: var(--gold); margin-bottom: 20px;
    }
    .pp-title {
        font-size: clamp(48px, 6vw, 76px);
        font-weight: 700; line-height: 0.95; letter-spacing: -1px;
        color: var(--kenya-white);
    }
    .pp-title span {
        background: linear-gradient(135deg, var(--gold), var(--gold-dim));
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .pp-date-badge {
        background: #141414; border: 1px solid rgba(255,255,255,0.07);
        border-radius: 12px; padding: 16px 22px; text-align: right; white-space: nowrap;
    }
    .pp-date-label { font-size: 9px; letter-spacing: 3px; text-transform: uppercase; color: rgba(255,255,255,0.25); }
    .pp-date-val { font-family: 'Oswald', sans-serif; font-size: 15px; font-weight: 600; color: rgba(255,255,255,0.6); margin-top: 4px; }

    /* Tri-color rule under title */
    .pp-rule {
        max-width: 1100px; margin: 0 auto 64px; padding: 0 32px;
        position: relative; z-index: 1;
    }
    .pp-rule-bar {
        height: 3px; border-radius: 2px;
        background: linear-gradient(90deg,
            var(--kenya-red)   0%   30%,
            var(--kenya-black) 30%  50%,
            var(--kenya-green) 50%  70%,
            var(--gold)        70% 100%
        );
        opacity: 0.7;
    }

    /* ── LAYOUT ── */
    .pp-layout {
        position: relative; z-index: 1;
        max-width: 1100px; margin: 0 auto; padding: 0 32px 100px;
        display: grid; grid-template-columns: 220px 1fr; gap: 56px; align-items: start;
    }

    /* ── STICKY SIDEBAR NAV ── */
    .pp-sidebar {
        position: sticky; top: 90px;
    }
    .pp-sidebar-label {
        font-size: 9px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase;
        color: rgba(255,255,255,0.2); margin-bottom: 16px;
    }
    .pp-sidebar-nav { display: flex; flex-direction: column; gap: 2px; }
    .pp-sidebar-link {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 14px; border-radius: 8px;
        font-size: 13px; font-weight: 500; color: rgba(255,255,255,0.35);
        text-decoration: none; transition: all 0.2s;
        border-left: 2px solid transparent;
    }
    .pp-sidebar-link:hover { color: var(--kenya-white); background: rgba(255,255,255,0.04); border-left-color: var(--gold); }
    .pp-sidebar-link.active { color: var(--kenya-white); border-left-color: var(--kenya-red); background: rgba(187,0,0,0.06); }
    .pp-sidebar-link .num {
        font-family: 'Oswald', sans-serif; font-size: 11px; font-weight: 700;
        color: rgba(255,255,255,0.18); width: 18px; flex-shrink: 0;
    }
    .pp-sidebar-link:hover .num { color: var(--gold); }

    /* ── CONTENT ── */
    .pp-content { min-width: 0; }

    .pp-section {
        margin-bottom: 56px;
        padding-bottom: 56px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        scroll-margin-top: 100px;
    }
    .pp-section:last-child { border-bottom: none; }

    .pp-section-header {
        display: flex; align-items: center; gap: 16px; margin-bottom: 24px;
    }
    .pp-section-num {
        font-family: 'Oswald', sans-serif; font-size: 13px; font-weight: 700;
        color: rgba(255,255,255,0.15); letter-spacing: 1px; flex-shrink: 0;
    }
    .pp-section h2 {
        font-size: 26px; font-weight: 700; letter-spacing: 0.3px;
        color: var(--kenya-white); line-height: 1.1;
    }

    /* Sub-sections */
    .pp-subsection { margin-top: 28px; }
    .pp-subsection h3 {
        font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
        color: var(--gold); margin-bottom: 12px;
        display: flex; align-items: center; gap: 8px;
    }
    .pp-subsection h3::before {
        content: ''; width: 20px; height: 1px; background: var(--gold); opacity: 0.5;
    }

    .pp-content p {
        font-size: 16px; line-height: 1.8; color: rgba(245,245,240,0.58);
        margin-bottom: 14px;
    }
    .pp-content p:last-child { margin-bottom: 0; }

    .pp-content strong {
        color: var(--kenya-white); font-weight: 600;
    }

    /* Styled list */
    .pp-list { list-style: none; display: flex; flex-direction: column; gap: 10px; margin-top: 4px; }
    .pp-list li {
        display: flex; align-items: flex-start; gap: 14px;
        font-size: 15px; color: rgba(245,245,240,0.58); line-height: 1.6;
    }
    .pp-list li::before {
        content: ''; flex-shrink: 0;
        width: 6px; height: 6px; border-radius: 50%;
        margin-top: 8px;
    }
    /* Color-coded bullets by section theme */
    .pp-list.red    li::before { background: var(--kenya-red); }
    .pp-list.green  li::before { background: var(--green-bright); }
    .pp-list.gold   li::before { background: var(--gold); }
    .pp-list.white  li::before { background: rgba(245,245,240,0.3); }

    /* ── CALLOUT CARDS ── */
    .pp-callout {
        border-radius: 14px; padding: 20px 24px; margin: 24px 0;
        display: flex; gap: 16px; align-items: flex-start;
    }
    .pp-callout.warn {
        background: rgba(187,0,0,0.08); border: 1px solid rgba(187,0,0,0.2);
    }
    .pp-callout.info {
        background: rgba(0,168,107,0.07); border: 1px solid rgba(0,168,107,0.18);
    }
    .pp-callout.gold {
        background: rgba(212,175,55,0.07); border: 1px solid rgba(212,175,55,0.2);
    }
    .pp-callout-icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
    .pp-callout.warn  .pp-callout-icon { color: #ff5555; }
    .pp-callout.info  .pp-callout-icon { color: var(--green-bright); }
    .pp-callout.gold  .pp-callout-icon { color: var(--gold); }
    .pp-callout-text { font-size: 14px; line-height: 1.65; color: rgba(245,245,240,0.65); }
    .pp-callout-text strong { color: rgba(245,245,240,0.9); }

    /* ── FOOTER ACKNOWLEDGEMENT ── */
    .pp-ack {
        position: relative; z-index: 1;
        max-width: 1100px; margin: 0 auto; padding: 0 32px 80px;
    }
    .pp-ack-inner {
        background: #111;
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 20px; padding: 40px 48px;
        display: flex; align-items: center; gap: 32px;
    }
    .pp-ack-flag {
        flex-shrink: 0; width: 6px; height: 80px; border-radius: 3px;
        background: linear-gradient(180deg, var(--kenya-red) 33%, #111 33% 66%, var(--kenya-green) 66%);
    }
    .pp-ack-text { font-size: 15px; color: rgba(245,245,240,0.4); line-height: 1.75; }
    .pp-ack-text strong { color: rgba(245,245,240,0.7); }

    /* ── FOOTER ── */
    footer {
        border-top: 1px solid rgba(255,255,255,0.05);
        background: #080808; padding: 40px 32px;
        text-align: center; position: relative; z-index: 1;
    }
    .footer-copy { font-size: 12px; color: rgba(255,255,255,0.18); }
    .footer-links { display: flex; justify-content: center; gap: 28px; margin-top: 12px; }
    .footer-links a { font-size: 12px; color: rgba(255,255,255,0.2); text-decoration: none; transition: color 0.2s; }
    .footer-links a:hover { color: rgba(255,255,255,0.5); }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .pp-hero { grid-template-columns: 1fr; }
        .pp-date-badge { text-align: left; }
        .pp-layout { grid-template-columns: 1fr; }
        .pp-sidebar { display: none; }
    }
</style>

<!-- Flag stripe -->
<div class="flag-stripe"></div>

<div class="pp-page">

    @include('components.frontend-nav')

    <!-- Hero header -->
    <div class="pp-hero">
        <div>
            <div class="pp-eyebrow">
                <i class="fas fa-shield-alt"></i>
                Legal Document
            </div>
            <h1 class="pp-title">Privacy<br><span>Policy</span></h1>
        </div>
        <div class="pp-date-badge">
            <div class="pp-date-label">Last Updated</div>
            <div class="pp-date-val">{{ date('F d, Y') }}</div>
        </div>
    </div>

    <!-- Tri-color rule -->
    <div class="pp-rule"><div class="pp-rule-bar"></div></div>

    <!-- Main layout -->
    <div class="pp-layout">

        <!-- Sidebar nav -->
        <aside class="pp-sidebar">
            <div class="pp-sidebar-label">Contents</div>
            <nav class="pp-sidebar-nav">
                <a href="#s1" class="pp-sidebar-link active"><span class="num">01</span> Introduction</a>
                <a href="#s2" class="pp-sidebar-link"><span class="num">02</span> Information We Collect</a>
                <a href="#s3" class="pp-sidebar-link"><span class="num">03</span> How We Use It</a>
                <a href="#s4" class="pp-sidebar-link"><span class="num">04</span> Data Sharing</a>
                <a href="#s5" class="pp-sidebar-link"><span class="num">05</span> Data Security</a>
                <a href="#s6" class="pp-sidebar-link"><span class="num">06</span> Your Rights</a>
                <a href="#s7" class="pp-sidebar-link"><span class="num">07</span> Cookies</a>
                <a href="#s8" class="pp-sidebar-link"><span class="num">08</span> Policy Changes</a>
            </nav>
        </aside>

        <!-- Content -->
        <main class="pp-content">

            <!-- 1 Introduction -->
            <section class="pp-section" id="s1">
                <div class="pp-section-header">
                    <span class="pp-section-num">01</span>
                    <h2>Introduction</h2>
                </div>
                <p>
                    Welcome to <strong>Tuko Kadi</strong>. We are committed to protecting your privacy while providing a platform
                    that helps young Kenyans register to vote and engage in civic discussions.
                </p>
                <div class="pp-callout info">
                    <i class="fas fa-leaf pp-callout-icon"></i>
                    <div class="pp-callout-text">
                        Tuko Kadi is a <strong>non-partisan youth civic initiative</strong>. We do not use your data for political campaigning or targeting.
                    </div>
                </div>
            </section>

            <!-- 2 Information We Collect -->
            <section class="pp-section" id="s2">
                <div class="pp-section-header">
                    <span class="pp-section-num">02</span>
                    <h2>Information We Collect</h2>
                </div>

                <div class="pp-subsection">
                    <h3>Personal Information</h3>
                    <p>We collect the following information when you use our platform:</p>
                    <ul class="pp-list red">
                        <li>Name, phone number, and email address</li>
                        <li>National ID details (for voter verification)</li>
                        <li>County and constituency</li>
                        <li>Age and gender (optional)</li>
                        <li>Voter registration status</li>
                    </ul>
                </div>

                <div class="pp-subsection">
                    <h3>Location Information</h3>
                    <p>
                        We collect location information including your <strong>county, constituency, and approximate GPS coordinates</strong>
                        when you send public messages or use location-based features. This helps us show relevant constituency chats
                        and nearby polling stations.
                    </p>
                </div>

                <div class="pp-subsection">
                    <h3>Public Messages</h3>
                    <div class="pp-callout warn">
                        <i class="fas fa-exclamation-triangle pp-callout-icon"></i>
                        <div class="pp-callout-text">
                            Messages you send in <strong>county or constituency chatrooms are public</strong>. They can be viewed by other users in the same constituency and may include your username and location data. These messages are not private.
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3 How We Use -->
            <section class="pp-section" id="s3">
                <div class="pp-section-header">
                    <span class="pp-section-num">03</span>
                    <h2>How We Use Your Information</h2>
                </div>
                <ul class="pp-list green">
                    <li>To facilitate voter registration and provide relevant civic information</li>
                    <li>To display public messages in constituency-based chatrooms</li>
                    <li>To show nearby polling stations based on your location</li>
                    <li>To generate anonymous statistics about voter registration trends</li>
                    <li>To improve our platform and user experience</li>
                </ul>
            </section>

            <!-- 4 Data Sharing -->
            <section class="pp-section" id="s4">
                <div class="pp-section-header">
                    <span class="pp-section-num">04</span>
                    <h2>Data Sharing</h2>
                </div>
                <p>We do not sell your personal data. Your information may be shared only in these cases:</p>
                <ul class="pp-list gold">
                    <li>With trusted service providers who help operate the platform</li>
                    <li>When required by law or government authorities (e.g., IEBC)</li>
                    <li>In aggregated, anonymized form for public awareness and research</li>
                </ul>
                <div class="pp-callout gold" style="margin-top:24px;">
                    <i class="fas fa-ban pp-callout-icon"></i>
                    <div class="pp-callout-text"><strong>We never sell your data</strong> to advertisers, political parties, or third-party marketers.</div>
                </div>
            </section>

            <!-- 5 Data Security -->
            <section class="pp-section" id="s5">
                <div class="pp-section-header">
                    <span class="pp-section-num">05</span>
                    <h2>Data Security</h2>
                </div>
                <p>
                    We take reasonable measures to protect your information using industry-standard encryption and access controls.
                    However, please note that public messages in constituency chats are visible to other users and cannot be made private.
                </p>
            </section>

            <!-- 6 Your Rights -->
            <section class="pp-section" id="s6">
                <div class="pp-section-header">
                    <span class="pp-section-num">06</span>
                    <h2>Your Rights</h2>
                </div>
                <p>You have the right to:</p>
                <ul class="pp-list white">
                    <li>Access or request correction of your personal data</li>
                    <li>Request deletion of your account and data (subject to legal requirements)</li>
                    <li>Opt out of certain data collection where possible</li>
                </ul>
            </section>

            <!-- 7 Cookies -->
            <section class="pp-section" id="s7">
                <div class="pp-section-header">
                    <span class="pp-section-num">07</span>
                    <h2>Cookies</h2>
                </div>
                <p>
                    We use cookies to improve your experience and analyze platform usage. You can manage cookie preferences
                    through your browser settings at any time.
                </p>
            </section>

            <!-- 8 Changes -->
            <section class="pp-section" id="s8">
                <div class="pp-section-header">
                    <span class="pp-section-num">08</span>
                    <h2>Changes to This Policy</h2>
                </div>
                <p>
                    We may update this Privacy Policy from time to time. Continued use of Tuko Kadi after changes
                    constitutes acceptance of the updated policy. We will notify users of significant changes via
                    the platform.
                </p>
            </section>

        </main>
    </div>

    <!-- Acknowledgement banner -->
    <div class="pp-ack">
        <div class="pp-ack-inner">
            <div class="pp-ack-flag"></div>
            <p class="pp-ack-text">
                By using <strong>Tuko Kadi</strong>, you acknowledge that you have read and understood this Privacy Policy,
                including the fact that <strong>location data and public messages are visible to others in your constituency</strong>.
                Your participation helps build a more democratic Kenya. 🇰🇪
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p class="footer-copy">&copy; {{ date('Y') }} Tuko Kadi. All rights reserved.</p>
        <div class="footer-links">
            <a href="{{ route('privacy') }}">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Contact Us</a>
        </div>
    </footer>

</div>

<!-- Flag stripe -->
<div class="flag-stripe"></div>

<script>
// Highlight active sidebar link on scroll
(function () {
    const links    = document.querySelectorAll('.pp-sidebar-link');
    const sections = document.querySelectorAll('.pp-section');
    if (!sections.length) return;

    const obs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                links.forEach(l => {
                    l.classList.toggle('active', l.getAttribute('href') === '#' + id);
                });
            }
        });
    }, { rootMargin: '-20% 0px -60% 0px' });

    sections.forEach(s => obs.observe(s));
})();
</script>

@endsection