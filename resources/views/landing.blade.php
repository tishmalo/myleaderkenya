@extends('layouts.landing')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap');

    :root {
        --kenya-red:    #BB0000;
        --kenya-black:  #111111;
        --kenya-white:  #F5F5F0;
        --gold:         #D4AF37;
        --green-bright: #00A86B;
        --red-bright:   #E8001A;
        --kenya-green:  #006600;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Barlow', sans-serif;
        background: var(--kenya-black);
        color: var(--kenya-white);
        overflow-x: hidden;
    }

    h1, h2, h3, h4, .display { font-family: 'Oswald', sans-serif; }

    /* ── Flag Stripe ── */
    .flag-stripe {
        height: 5px;
        background: linear-gradient(90deg,
            var(--kenya-black) 0% 33.3%,
            var(--kenya-red)   33.3% 66.6%,
            var(--kenya-green) 66.6% 100%);
    }

    /* ════════════════════════════════
       NAV
    ════════════════════════════════ */
    nav.main-nav {
        background: rgba(10,10,10,0.97);
        border-bottom: 1px solid rgba(255,255,255,0.07);
        backdrop-filter: blur(16px);
        position: sticky; top: 5px; z-index: 100;
    }
    .nav-inner {
        max-width: 1280px; margin: 0 auto;
        padding: 18px 32px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .brand { display: flex; align-items: center; gap: 14px; text-decoration: none; }
    .brand-logo { width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; }
    .logo-img { width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }
    .brand-text { line-height: 1.1; }
    .brand-name { font-family: 'Oswald', sans-serif; font-size: 22px; font-weight: 700; color: white; letter-spacing: 1px; }
    .brand-sub  { font-size: 11px; color: var(--green-bright); letter-spacing: 2px; margin-top: -2px; font-weight: 500; }

    .nav-links { display: flex; gap: 36px; }
    .nav-links a {
        font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.55);
        text-decoration: none; letter-spacing: 1.5px; text-transform: uppercase; transition: color 0.2s;
    }
    .nav-links a:hover { color: var(--kenya-white); }

    .nav-cta { display: flex; gap: 12px; align-items: center; }
    .btn-ghost {
        padding: 9px 20px; font-size: 13px; font-weight: 600;
        color: rgba(255,255,255,0.5); letter-spacing: 1px; text-transform: uppercase;
        background: none; border: none; cursor: pointer; text-decoration: none;
        transition: color 0.2s;
    }
    .btn-ghost:hover { color: white; }
    .btn-primary {
        padding: 10px 24px; font-size: 13px; font-weight: 700;
        background: var(--kenya-red); color: white; border-radius: 6px;
        letter-spacing: 1px; text-transform: uppercase;
        transition: background 0.2s, transform 0.15s;
        border: none; cursor: pointer; text-decoration: none;
    }
    .btn-primary:hover { background: #d00; transform: translateY(-1px); }

    /* ════════════════════════════════
       AUTH MODAL
    ════════════════════════════════ */
    .auth-modal-backdrop {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,0.80);
        backdrop-filter: blur(8px);
        display: flex; align-items: center; justify-content: center;
        padding: 20px;
        opacity: 0; pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .auth-modal-backdrop.open { opacity: 1; pointer-events: all; }

    .auth-modal {
        background: #141414;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 24px;
        width: 100%; max-width: 960px;
        max-height: 92vh; overflow-y: auto;
        position: relative;
        transform: translateY(24px) scale(0.97);
        transition: transform 0.28s cubic-bezier(0.34,1.56,0.64,1);
        box-shadow: 0 40px 80px rgba(0,0,0,0.7);
    }
    .auth-modal-backdrop.open .auth-modal { transform: translateY(0) scale(1); }

    .auth-modal-stripe {
        height: 4px;
        border-radius: 24px 24px 0 0;
        background: linear-gradient(90deg, var(--kenya-green) 33%, #111 33% 66%, var(--kenya-red) 66%);
    }

    .auth-modal-close {
        position: absolute; top: 18px; right: 18px;
        width: 36px; height: 36px;
        background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
        color: rgba(255,255,255,0.5); border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 18px; transition: all 0.2s; z-index: 1;
    }
    .auth-modal-close:hover { background: rgba(255,255,255,0.14); color: white; }

    .auth-modal-inner {
        display: grid; grid-template-columns: 340px 1fr;
        min-height: 520px;
    }

    /* Left branding panel */
    .auth-panel-left {
        background: linear-gradient(145deg, rgba(187,0,0,0.12) 0%, rgba(0,102,0,0.1) 100%);
        border-right: 1px solid rgba(255,255,255,0.06);
        padding: 48px 36px;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        text-align: center; gap: 18px;
    }
    .auth-panel-logo {
        width: 72px; height: 72px;
        background: rgba(187,0,0,0.15); border: 1px solid rgba(187,0,0,0.3);
        border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 4px;
    }
    .auth-panel-logo img { width: 52px; height: 52px; object-fit: contain; }
    .auth-panel-title { font-family: 'Oswald', sans-serif; font-size: 26px; font-weight: 700; color: white; }
    .auth-panel-sub   { font-size: 14px; color: rgba(245,245,240,0.45); line-height: 1.7; max-width: 220px; }
    .auth-panel-flags { display: flex; gap: 8px; margin-top: 8px; }
    .auth-flag-bar    { height: 4px; width: 40px; border-radius: 2px; }

    /* Right forms panel */
    .auth-panel-right { padding: 40px 44px 48px; }

    /* Tabs */
    .auth-tabs {
        display: flex;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        margin-bottom: 30px;
    }
    .auth-tab {
        flex: 1; padding: 14px 16px;
        font-family: 'Oswald', sans-serif; font-size: 15px; font-weight: 600;
        letter-spacing: 1px; text-transform: uppercase;
        color: rgba(255,255,255,0.35);
        background: none; border: none; cursor: pointer;
        border-bottom: 2px solid transparent; margin-bottom: -1px;
        transition: all 0.2s;
    }
    .auth-tab.active { color: var(--green-bright); border-bottom-color: var(--green-bright); }
    .auth-tab:hover:not(.active) { color: rgba(255,255,255,0.6); }

    /* Form panels */
    .auth-form-panel { display: none; }
    .auth-form-panel.active { display: block; }

    /* Error box */
    .auth-errors {
        background: rgba(187,0,0,0.1); border: 1px solid rgba(187,0,0,0.3);
        border-radius: 10px; padding: 14px 18px; margin-bottom: 20px;
        font-size: 13px; color: #ff8888;
    }
    .auth-errors ul { padding-left: 16px; }
    .auth-errors li { margin-bottom: 4px; }

    /* Fields */
    .auth-field { margin-bottom: 16px; }
    .auth-field label {
        display: block; font-size: 11px; font-weight: 700;
        letter-spacing: 1.5px; text-transform: uppercase;
        color: rgba(245,245,240,0.38); margin-bottom: 7px;
    }
    .auth-field-wrap { position: relative; }
    .auth-field-icon {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: rgba(255,255,255,0.2); font-size: 13px; pointer-events: none;
        transition: color 0.2s;
    }
    .auth-field-wrap:focus-within .auth-field-icon { color: var(--green-bright); }

    .auth-field input,
    .auth-field select {
        width: 100%;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        padding: 13px 16px 13px 40px;
        color: white; font-size: 14px; font-family: 'Barlow', sans-serif;
        transition: border-color 0.2s, box-shadow 0.2s;
        appearance: none;
    }
    .auth-field input:focus,
    .auth-field select:focus {
        outline: none;
        border-color: var(--green-bright);
        box-shadow: 0 0 0 3px rgba(0,168,107,0.12);
    }
    .auth-field input::placeholder { color: rgba(255,255,255,0.18); }
    .auth-field select option { background: #1c1c1c; color: white; }

    /* password toggle */
    .auth-pwd-toggle {
        position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer;
        color: rgba(255,255,255,0.22); font-size: 13px; padding: 4px;
        transition: color 0.2s;
    }
    .auth-pwd-toggle:hover { color: rgba(255,255,255,0.6); }

    .auth-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    /* Divider */
    .auth-divider {
        display: flex; align-items: center; gap: 12px;
        margin: 18px 0 16px; color: rgba(255,255,255,0.2);
        font-size: 10px; letter-spacing: 2px;
    }
    .auth-divider::before, .auth-divider::after {
        content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.07);
    }

    /* Submit */
    .auth-submit {
        width: 100%; padding: 15px;
        font-family: 'Oswald', sans-serif; font-size: 15px; font-weight: 700;
        letter-spacing: 1.5px; text-transform: uppercase;
        background: var(--kenya-red); color: white;
        border: none; border-radius: 10px; cursor: pointer;
        transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        margin-top: 6px;
        box-shadow: 0 0 24px rgba(187,0,0,0.25);
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .auth-submit:hover { background: #cc0000; transform: translateY(-1px); box-shadow: 0 0 36px rgba(187,0,0,0.4); }
    .auth-submit:active { transform: translateY(0); }

    .auth-bottom-link {
        text-align: center; margin-top: 18px;
        font-size: 13px; color: rgba(255,255,255,0.38);
    }
    .auth-bottom-link button {
        background: none; border: none; cursor: pointer;
        color: var(--green-bright); font-weight: 600; font-size: 13px;
    }
    .auth-bottom-link button:hover { text-decoration: underline; }

    /* Password strength */
    .pwd-strength-bar {
        height: 3px; background: rgba(255,255,255,0.06);
        border-radius: 2px; overflow: hidden; margin-top: 7px;
    }
    .pwd-strength-fill { height: 100%; width: 0%; border-radius: 2px; transition: width 0.3s, background 0.3s; }
    .pwd-strength-label { font-size: 10px; margin-top: 4px; color: rgba(255,255,255,0.25); }

    /* Responsive */
    @media (max-width: 720px) {
        .auth-modal-inner { grid-template-columns: 1fr; }
        .auth-panel-left  { display: none; }
        .auth-panel-right { padding: 28px 24px 36px; }
        .auth-grid-2      { grid-template-columns: 1fr; }
    }

    /* ════════════════════════════════
       HERO
    ════════════════════════════════ */
    .hero {
        position: relative; min-height: 92vh;
        display: flex; align-items: center;
        overflow: hidden; background: #0a0a0a;
    }
    #hero-slider { position: absolute; inset: 0; overflow: hidden; z-index: 0; }
    .hero-slide {
        position: absolute; inset: 0;
        background-size: cover; background-position: center;
        opacity: 0; transform: scale(1.08);
        transition: opacity 1.4s ease-in-out, transform 8s ease-in-out;
    }
    .hero-slide.active { opacity: 1; transform: scale(1.0); }
    .hero-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.62); z-index: 1; }

    .hero-orb-1 { position: absolute; width: 75vw; height: 75vw; max-width: 900px; max-height: 900px; border-radius: 50%; background: radial-gradient(circle, rgba(220,0,0,0.35) 0%, rgba(187,0,0,0.12) 40%, transparent 70%); top: -20%; right: -15%; animation: orbDrift1 12s ease-in-out infinite alternate; filter: blur(2px); z-index: 2; pointer-events: none; }
    .hero-orb-2 { position: absolute; width: 65vw; height: 65vw; max-width: 800px; max-height: 800px; border-radius: 50%; background: radial-gradient(circle, rgba(0,180,80,0.28) 0%, rgba(0,120,40,0.1) 45%, transparent 70%); bottom: -25%; left: -10%; animation: orbDrift2 15s ease-in-out infinite alternate; filter: blur(2px); z-index: 2; pointer-events: none; }
    .hero-orb-3 { position: absolute; width: 45vw; height: 45vw; max-width: 600px; max-height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(200,0,50,0.22) 0%, transparent 65%); top: 40%; left: 30%; animation: orbDrift3 18s ease-in-out infinite alternate; filter: blur(4px); z-index: 2; pointer-events: none; }
    .hero-orb-4 { position: absolute; width: 40vw; height: 40vw; max-width: 500px; max-height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(0,210,100,0.16) 0%, transparent 65%); top: -10%; left: 20%; animation: orbDrift4 10s ease-in-out infinite alternate; filter: blur(3px); z-index: 2; pointer-events: none; }

    @keyframes orbDrift1 { 0%{transform:translate(0,0) scale(1)} 33%{transform:translate(-8%,12%) scale(1.08)} 66%{transform:translate(-14%,5%) scale(0.95)} 100%{transform:translate(-6%,18%) scale(1.05)} }
    @keyframes orbDrift2 { 0%{transform:translate(0,0) scale(1)} 33%{transform:translate(10%,-8%) scale(1.1)} 66%{transform:translate(5%,-15%) scale(0.93)} 100%{transform:translate(12%,-6%) scale(1.06)} }
    @keyframes orbDrift3 { 0%{transform:translate(0,0) scale(1)} 50%{transform:translate(-12%,10%) scale(1.15)} 100%{transform:translate(8%,-8%) scale(0.9)} }
    @keyframes orbDrift4 { 0%{transform:translate(0,0) scale(1)} 50%{transform:translate(8%,12%) scale(1.2)} 100%{transform:translate(-5%,6%) scale(0.88)} }

    .hero-bg-texture { position: absolute; inset: 0; z-index: 3; pointer-events: none; background-image: repeating-linear-gradient(-45deg, transparent, transparent 40px, rgba(255,255,255,0.012) 40px, rgba(255,255,255,0.012) 41px); }
    .hero-bg-vignette { position: absolute; inset: 0; z-index: 3; pointer-events: none; background: radial-gradient(ellipse 85% 85% at 50% 50%, transparent 35%, rgba(0,0,0,0.5) 100%); }
    .hero-flag-deco { position: absolute; right: -60px; top: -40px; width: 460px; height: 580px; opacity: 0.06; display: flex; flex-direction: column; border-radius: 30px; overflow: hidden; transform: rotate(12deg); z-index: 3; pointer-events: none; }
    .hero-flag-deco .f1 { flex: 1; background: var(--kenya-black); }
    .hero-flag-deco .f2 { flex: 1.2; background: var(--kenya-red); }
    .hero-flag-deco .f3 { flex: 1; background: var(--kenya-green); }

    .slider-dots { position: absolute; bottom: 24px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10; }
    .slider-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.3); cursor: pointer; transition: background 0.3s, transform 0.3s; border: none; padding: 0; }
    .slider-dot.active { background: white; transform: scale(1.3); }

    .hero-inner { position: relative; z-index: 5; max-width: 1280px; margin: 0 auto; padding: 80px 32px; display: grid; grid-template-columns: 1fr 420px; gap: 64px; align-items: center; width: 100%; }

    .hero-badge { display: inline-flex; align-items: center; gap: 10px; border: 1px solid rgba(187,0,0,0.5); background: rgba(187,0,0,0.1); padding: 8px 20px; border-radius: 40px; font-size: 11px; font-weight: 700; letter-spacing: 2.5px; color: #ff6666; text-transform: uppercase; margin-bottom: 28px; }
    .hero-badge .dot { width: 6px; height: 6px; background: var(--red-bright); border-radius: 50%; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(1.5)} }

    .hero-headline { font-size: clamp(56px, 7vw, 88px); line-height: 0.95; font-weight: 700; letter-spacing: -1px; margin-bottom: 28px; }
    .hero-headline .line-1 { color: var(--kenya-white); display: block; }
    .hero-headline .line-2 { display: block; }
    .hero-headline .line-2 span { background: linear-gradient(135deg, var(--green-bright), var(--kenya-green)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    .hero-sub { font-size: 19px; line-height: 1.65; color: rgba(245,245,240,0.65); max-width: 480px; margin-bottom: 44px; }

    .hero-actions { display: flex; gap: 16px; flex-wrap: wrap; }
    .btn-hero-primary { display: inline-flex; align-items: center; gap: 10px; padding: 18px 36px; font-size: 15px; font-weight: 700; font-family: 'Oswald', sans-serif; letter-spacing: 2px; text-transform: uppercase; background: var(--kenya-red); color: white; border-radius: 6px; text-decoration: none; box-shadow: 0 0 40px rgba(187,0,0,0.4); transition: all 0.25s; cursor: pointer; border: none; }
    .btn-hero-primary:hover { background: #cc0000; box-shadow: 0 0 60px rgba(187,0,0,0.6); transform: translateY(-2px); }
    .btn-hero-secondary { display: inline-flex; align-items: center; gap: 10px; padding: 18px 36px; font-size: 15px; font-weight: 700; font-family: 'Oswald', sans-serif; letter-spacing: 2px; text-transform: uppercase; border: 2px solid rgba(0,168,107,0.5); color: var(--green-bright); border-radius: 6px; text-decoration: none; transition: all 0.25s; }
    .btn-hero-secondary:hover { border-color: var(--green-bright); background: rgba(0,168,107,0.08); }

    .hero-divider { display: flex; align-items: center; gap: 16px; margin-top: 52px; }
    .hero-divider::before, .hero-divider::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1)); }
    .hero-divider::after { background: linear-gradient(90deg, rgba(255,255,255,0.1), transparent); }
    .hero-divider-text { font-size: 10px; font-weight: 700; letter-spacing: 3px; color: rgba(255,255,255,0.25); text-transform: uppercase; white-space: nowrap; }

    /* Hero Card */
    .hero-card { background: rgba(22,22,22,0.92); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; overflow: hidden; backdrop-filter: blur(12px); }
    .hero-card-header { padding: 28px 28px 0; display: flex; align-items: center; gap: 10px; }
    .hc-dot { width: 12px; height: 12px; border-radius: 50%; }
    .hero-card-banner { margin: 20px 28px; background: #0c0c0c; border-radius: 12px; padding: 40px 24px; text-align: center; position: relative; overflow: hidden; }
    .hero-card-banner::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(187,0,0,0.08) 0%, rgba(0,102,0,0.08) 100%); }
    .banner-icon { font-size: 80px; color: rgba(255,255,255,0.05); position: relative; z-index: 1; }
    .banner-tagline { font-family: 'Oswald', sans-serif; font-size: 22px; font-weight: 600; color: var(--kenya-white); position: relative; z-index: 1; margin-top: 12px; }
    .tri-underline { height: 3px; border-radius: 2px; margin: 8px auto 0; width: 120px; background: linear-gradient(90deg, var(--kenya-red) 33%, var(--kenya-black) 33% 66%, var(--kenya-green) 66%); }
    .hero-card-body { padding: 8px 28px 28px; }
    .hc-title { font-family: 'Oswald', sans-serif; font-size: 18px; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 20px; }
    .reason-list { display: flex; flex-direction: column; gap: 18px; }
    .reason-item { display: flex; gap: 16px; align-items: flex-start; }
    .reason-num { font-family: 'Oswald', sans-serif; font-size: 30px; font-weight: 700; line-height: 1; flex-shrink: 0; width: 36px; }
    .reason-item:nth-child(1) .reason-num { color: var(--kenya-red); }
    .reason-item:nth-child(2) .reason-num { color: var(--kenya-white); }
    .reason-item:nth-child(3) .reason-num { color: var(--green-bright); }
    .reason-title { font-weight: 600; font-size: 15px; margin-bottom: 3px; }
    .reason-desc  { font-size: 13px; color: rgba(245,245,240,0.5); line-height: 1.5; }

    /* ════════════════════════════════
       SECTION STRIPE
    ════════════════════════════════ */
    .section-stripe { height: 6px; background: linear-gradient(90deg, var(--kenya-green) 0% 33.3%, #111 33.3% 66.6%, var(--kenya-red) 66.6% 100%); }

    /* ════════════════════════════════
       ANALYTICS
    ════════════════════════════════ */
    .analytics-section { background: #0d0d0d; padding: 100px 0; }
    .section-inner  { max-width: 1280px; margin: 0 auto; padding: 0 32px; }
    .section-header { text-align: center; margin-bottom: 64px; }
    .section-label  { display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 4px; text-transform: uppercase; color: var(--kenya-red); margin-bottom: 16px; }
    .section-title  { font-size: clamp(36px, 4vw, 52px); font-weight: 700; line-height: 1.05; }
    .section-sub    { font-size: 17px; color: rgba(245,245,240,0.45); margin-top: 12px; }

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 48px; }
    .stat-card { border-radius: 16px; padding: 32px 24px; border: 1px solid rgba(255,255,255,0.06); position: relative; overflow: hidden; text-align: center; }
    .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
    .stat-card.green { background: rgba(0,102,0,0.12); }    .stat-card.green::before { background: var(--green-bright); }
    .stat-card.white { background: rgba(255,255,255,0.04); } .stat-card.white::before { background: rgba(255,255,255,0.3); }
    .stat-card.red   { background: rgba(187,0,0,0.12); }    .stat-card.red::before   { background: var(--kenya-red); }
    .stat-card.pink  { background: rgba(232,0,100,0.1); }   .stat-card.pink::before  { background: #e80064; }
    .stat-num { font-family: 'Oswald', sans-serif; font-size: 50px; font-weight: 700; line-height: 1; }
    .stat-card.green .stat-num { color: var(--green-bright); }
    .stat-card.white .stat-num { color: var(--kenya-white); }
    .stat-card.red   .stat-num { color: #ff5555; }
    .stat-card.pink  .stat-num { color: #ff6eb4; }
    .stat-label { font-size: 13px; color: rgba(245,245,240,0.45); margin-top: 8px; letter-spacing: 0.5px; }
    .stat-meta  { font-size: 11px; color: rgba(245,245,240,0.3); margin-top: 6px; letter-spacing: 0.5px; min-height: 16px; }
    .stat-meta span { color: var(--green-bright); }
    .live-badge { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:10px; }
    .live-dot   { width:7px; height:7px; border-radius:50%; display:inline-block; animation:pulse 1.5s infinite; }
    .live-text  { font-size:10px; letter-spacing:2px; color:rgba(245,245,240,0.35); text-transform:uppercase; font-weight:700; }

    .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .chart-card  { background: #161616; border: 1px solid rgba(255,255,255,0.07); border-radius: 20px; padding: 32px; }
    .chart-card-title { font-family: 'Oswald', sans-serif; font-size: 20px; font-weight: 600; margin-bottom: 24px; letter-spacing: 0.5px; }
    .chart-wrap { height: 340px; }

    .county-list-card { background: #161616; border: 1px solid rgba(255,255,255,0.07); border-radius: 20px; padding: 32px; margin-top: 24px; }
    .county-table { width: 100%; border-collapse: collapse; }
    .county-table th { font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: rgba(245,245,240,0.3); padding: 0 0 12px; text-align: left; }
    .county-table th:last-child { text-align: right; }
    .county-table td { padding: 12px 0; border-top: 1px solid rgba(255,255,255,0.04); font-size: 14px; }
    .county-table td:last-child { text-align: right; }
    .county-badge { display: inline-block; background: rgba(0,168,107,0.12); color: var(--green-bright); border-radius: 20px; padding: 3px 12px; font-size: 12px; font-weight: 600; }
    .county-rank  { font-family: 'Oswald', sans-serif; font-size: 18px; font-weight: 700; color: rgba(245,245,240,0.15); margin-right: 16px; }

    /* Scrollable Container */
    .county-table-scroll {
        max-height: 480px;
        overflow-y: auto;
        padding-right: 12px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.1) transparent;
    }
    .county-table-scroll::-webkit-scrollbar { width: 6px; }
    .county-table-scroll::-webkit-scrollbar-track { background: transparent; }
    .county-table-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    .county-table-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

    /* ════════════════════════════════
       HOW IT WORKS
    ════════════════════════════════ */
    .how-section { padding: 100px 0; background: var(--kenya-black); }
    .steps-grid  { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .step-card { background: #141414; border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; padding: 44px 36px; text-align: center; position: relative; overflow: hidden; transition: border-color 0.3s, transform 0.3s; }
    .step-card:hover { border-color: rgba(255,255,255,0.2); transform: translateY(-4px); }
    .step-card::before { content: ''; position: absolute; top: 0; left: 0; width: 80px; height: 4px; border-radius: 0 0 4px 0; }
    .step-card:nth-child(1)::before { background: var(--kenya-green); }
    .step-card:nth-child(2)::before { background: var(--kenya-red); }
    .step-card:nth-child(3)::before { background: var(--kenya-white); }
    .step-num { font-family: 'Oswald', sans-serif; font-size: 72px; font-weight: 700; line-height: 1; margin-bottom: 20px; }
    .step-card:nth-child(1) .step-num { color: rgba(0,168,107,0.2); }
    .step-card:nth-child(2) .step-num { color: rgba(187,0,0,0.2); }
    .step-card:nth-child(3) .step-num { color: rgba(255,255,255,0.1); }
    .step-title { font-family: 'Oswald', sans-serif; font-size: 22px; font-weight: 600; margin-bottom: 12px; }
    .step-desc  { font-size: 15px; color: rgba(245,245,240,0.5); line-height: 1.6; }

    /* ════════════════════════════════
       ABOUT
    ════════════════════════════════ */
    .about-section { background: #0d0d0d; padding: 100px 0; border-top: 1px solid rgba(255,255,255,0.05); }
    .about-inner   { max-width: 760px; margin: 0 auto; padding: 0 32px; text-align: center; }
    .about-text    { font-size: 19px; color: rgba(245,245,240,0.5); line-height: 1.8; }

    /* ════════════════════════════════
       CTA
    ════════════════════════════════ */
    .cta-section { padding: 100px 32px; text-align: center; position: relative; overflow: hidden; }
    .cta-section::before { content: '🇰🇪'; position: absolute; font-size: 320px; opacity: 0.03; top: 50%; left: 50%; transform: translate(-50%,-50%); pointer-events: none; }
    .cta-sub  { font-size: 20px; color: rgba(245,245,240,0.35); }
    .cta-main { font-family: 'Oswald', sans-serif; font-size: clamp(32px, 4vw, 52px); font-weight: 700; margin-top: 12px; }
    .cta-tag  { display: inline-block; margin-top: 40px; font-family: 'Oswald', sans-serif; font-size: clamp(28px, 3.5vw, 44px); font-weight: 700; }
    .cta-tag .t1 { color: var(--kenya-red); }
    .cta-tag .t2 { color: var(--kenya-white); }
    .cta-tag .t3 { color: var(--green-bright); }

    /* ════════════════════════════════
       FOOTER
    ════════════════════════════════ */
    footer { border-top: 1px solid rgba(255,255,255,0.06); background: #080808; padding: 52px 32px; text-align: center; }
    .footer-logo  { font-family: 'Oswald', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: 2px; color: rgba(255,255,255,0.2); margin-bottom: 20px; }
    .footer-copy  { font-size: 13px; color: rgba(255,255,255,0.2); }
    .footer-links { display: flex; justify-content: center; gap: 32px; margin-top: 16px; }
    .footer-links a { font-size: 13px; color: rgba(255,255,255,0.25); text-decoration: none; transition: color 0.2s; }
    .footer-links a:hover { color: rgba(255,255,255,0.6); }

    /* ════════════════════════════════
       RESPONSIVE
    ════════════════════════════════ */
    @media (max-width: 900px) {
        .hero-inner  { grid-template-columns: 1fr; }
        .hero-card   { display: none; }
        .stats-grid  { grid-template-columns: 1fr 1fr; }
        .charts-grid { grid-template-columns: 1fr; }
        .steps-grid  { grid-template-columns: 1fr; }
        .nav-links   { display: none; }
    }
</style>

<!-- ══════════════════════════════════════════════════
     AUTH MODAL
══════════════════════════════════════════════════ -->
<div class="auth-modal-backdrop" id="authModal" onclick="handleBackdropClick(event)">
    <div class="auth-modal" role="dialog" aria-modal="true" aria-label="Login or Register">
        <div class="auth-modal-stripe"></div>
        <button class="auth-modal-close" onclick="closeModal()" aria-label="Close">&times;</button>

        <div class="auth-modal-inner">

            <!-- Left brand panel -->
            <div class="auth-panel-left">
                <div class="auth-panel-logo">
                    <img src="{{ asset('images/myleader.png') }}" alt="My Leader Kenya">
                </div>
                <div class="auth-panel-title">TUKO KADI</div>
                <p class="auth-panel-sub">Kenya's youth voter movement.<br>Register. Vote. Lead.</p>
                <div class="auth-panel-flags">
                    <div class="auth-flag-bar" style="background:var(--kenya-green)"></div>
                    <div class="auth-flag-bar" style="background:var(--kenya-black);border:1px solid rgba(255,255,255,0.1)"></div>
                    <div class="auth-flag-bar" style="background:var(--kenya-red)"></div>
                </div>
            </div>

            <!-- Right forms panel -->
            <div class="auth-panel-right">

                <div class="auth-tabs">
                    <button class="auth-tab active" id="tab-login"    onclick="switchTab('login')">Login</button>
                    <button class="auth-tab"         id="tab-register" onclick="switchTab('register')">Join Now</button>
                </div>

                @if ($errors->any())
                <div class="auth-errors">
                    <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <!-- ── LOGIN ── -->
                <div class="auth-form-panel active" id="panel-login">
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_form_type" value="login">

                        <div class="auth-field">
                            <label>Username</label>
                            <div class="auth-field-wrap">
                                <input type="text" name="username" placeholder="your_username"
                                       value="{{ old('username') }}" required autocomplete="username" autofocus>
                                <span class="auth-field-icon"><i class="fas fa-user"></i></span>
                            </div>
                        </div>

                        <div class="auth-field">
                            <label>Password</label>
                            <div class="auth-field-wrap">
                                <input type="password" id="modal-login-pwd" name="password"
                                       placeholder="••••••••" required autocomplete="current-password">
                                <span class="auth-field-icon"><i class="fas fa-lock"></i></span>
                                <button type="button" class="auth-pwd-toggle"
                                        onclick="togglePwd('modal-login-pwd',this)" aria-label="Toggle password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="auth-submit">
                            <i class="fas fa-sign-in-alt"></i> Login to My Account
                        </button>
                    </form>
                    <div class="auth-bottom-link">
                        Don't have an account? <button onclick="switchTab('register')">Register now →</button>
                    </div>
                </div>

                <!-- ── REGISTER ── -->
                <div class="auth-form-panel" id="panel-register">
                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_form_type" value="register">

                        <div class="auth-grid-2">
                            <div class="auth-field">
                                <label>Username <span style="color:var(--kenya-red)">*</span></label>
                                <div class="auth-field-wrap">
                                    <input type="text" name="username" placeholder="johndoe"
                                           value="{{ old('username') }}" required>
                                    <span class="auth-field-icon"><i class="fas fa-at"></i></span>
                                </div>
                            </div>
                            <div class="auth-field">
                                <label>Full Name <span style="color:var(--kenya-red)">*</span></label>
                                <div class="auth-field-wrap">
                                    <input type="text" name="name" placeholder="John Doe"
                                           value="{{ old('name') }}" required>
                                    <span class="auth-field-icon"><i class="fas fa-user"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="auth-grid-2">
                            <div class="auth-field">
                                <label>Email</label>
                                <div class="auth-field-wrap">
                                    <input type="email" name="email" placeholder="john@email.com"
                                           value="{{ old('email') }}">
                                    <span class="auth-field-icon"><i class="fas fa-envelope"></i></span>
                                </div>
                            </div>
                            <div class="auth-field">
                                <label>Phone</label>
                                <div class="auth-field-wrap">
                                    <input type="tel" name="phone" placeholder="07XX XXX XXX"
                                           value="{{ old('phone') }}">
                                    <span class="auth-field-icon"><i class="fas fa-phone"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="auth-grid-2">
                            <div class="auth-field">
                                <label>Gender</label>
                                <div class="auth-field-wrap">
                                    <select name="gender">
                                        <option value="male"   {{ old('gender')=='male'   ?'selected':'' }}>Male</option>
                                        <option value="female" {{ old('gender')=='female' ?'selected':'' }}>Female</option>
                                        <option value="other"  {{ old('gender')=='other'  ?'selected':'' }}>Other</option>
                                    </select>
                                    <span class="auth-field-icon"><i class="fas fa-venus-mars"></i></span>
                                </div>
                            </div>
                            <div class="auth-field">
                                <label>Year of Birth</label>
                                <div class="auth-field-wrap">
                                    <input type="number" name="year_of_birth" placeholder="2000"
                                           min="1900" max="{{ date('Y') }}" value="{{ old('year_of_birth') }}">
                                    <span class="auth-field-icon"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="auth-divider">Password</div>

                        <div class="auth-grid-2">
                            <div class="auth-field">
                                <label>Password <span style="color:var(--kenya-red)">*</span></label>
                                <div class="auth-field-wrap">
                                    <input type="password" id="modal-reg-pwd" name="password"
                                           placeholder="••••••••" required
                                           oninput="modalPwdStrength(this.value)">
                                    <span class="auth-field-icon"><i class="fas fa-lock"></i></span>
                                    <button type="button" class="auth-pwd-toggle"
                                            onclick="togglePwd('modal-reg-pwd',this)" aria-label="Toggle password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="pwd-strength-bar"><div class="pwd-strength-fill" id="modal-pwd-fill"></div></div>
                                <div class="pwd-strength-label" id="modal-pwd-label"></div>
                            </div>
                            <div class="auth-field">
                                <label>Confirm Password <span style="color:var(--kenya-red)">*</span></label>
                                <div class="auth-field-wrap">
                                    <input type="password" id="modal-reg-pwd2" name="password_confirmation"
                                           placeholder="••••••••" required>
                                    <span class="auth-field-icon"><i class="fas fa-lock"></i></span>
                                    <button type="button" class="auth-pwd-toggle"
                                            onclick="togglePwd('modal-reg-pwd2',this)" aria-label="Toggle password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="auth-submit">
                            <i class="fas fa-user-plus"></i> Create My Account
                        </button>
                    </form>
                    <div class="auth-bottom-link">
                        Already have an account? <button onclick="switchTab('login')">Login here →</button>
                    </div>
                </div>

            </div><!-- /auth-panel-right -->
        </div><!-- /auth-modal-inner -->
    </div><!-- /auth-modal -->
</div><!-- /backdrop -->


<!-- ══════════════════════════════════════════════════
     PAGE
══════════════════════════════════════════════════ -->
<div>
    <div class="flag-stripe"></div>

    @include('components.frontend-nav')


    <!-- HERO -->
<section class="hero">
    <div id="hero-slider">
        <div class="hero-slide active" style="background-image: url('{{ asset('images/ml1.jpg') }}')"></div>
        <div class="hero-slide"        style="background-image: url('{{ asset('images/ml2.jpg') }}')"></div>
        <div class="hero-slide"        style="background-image: url('{{ asset('images/ml3.jpg') }}')"></div>
        <div class="hero-slide"        style="background-image: url('{{ asset('images/ml4.jpg') }}')"></div>
        <div class="hero-slide"        style="background-image: url('{{ asset('images/ml5.jpg') }}')"></div>
    </div>

    <div class="hero-overlay"></div>
    <div class="hero-orb-1"></div>
    <div class="hero-orb-2"></div>
    <div class="hero-orb-3"></div>
    <div class="hero-orb-4"></div>
    <div class="hero-bg-texture"></div>
    <div class="hero-bg-vignette"></div>
    <div class="hero-flag-deco">
        <div class="f1"></div>
        <div class="f2"></div>
        <div class="f3"></div>
    </div>

    <div class="hero-inner">
        <div>
            <div class="hero-badge"><span class="dot"></span>Youth Voter Movement 2027</div>
            <h1 class="hero-headline">
                <span class="line-1">Niko Kadi,</span>
                <span class="line-2"><span>Je Wewe?</span></span>
            </h1>
            <p class="hero-sub">
                Your future. Your voice. Your vote.<br>
                Young Kenyans are rising — don't be left behind.
            </p>
            <div class="hero-actions">
                <a href="https://play.google.com/store/apps/details?id=com.mlk.tukokadi"
                   target="_blank" rel="noopener noreferrer" class="btn-hero-primary">
                    <i class="fas fa-mobile-alt"></i> Download App
                    <i class="fas fa-external-link-alt" style="font-size:11px;opacity:0.7"></i>
                </a>
                <a href="#analytics" class="btn-hero-secondary">
                    <i class="fas fa-chart-bar"></i> Live Stats
                </a>
            </div>
            <div class="hero-divider">
                <span class="hero-divider-text">TUKO KADI &bull; JE WEWE? &bull; 2027 &bull; KENYA</span>
            </div>
        </div>

        <div class="hero-card">
            <div class="hero-card-header">
                <div class="hc-dot" style="background:var(--kenya-red)"></div>
                <div class="hc-dot" style="background:rgba(255,255,255,0.15)"></div>
                <div class="hc-dot" style="background:var(--kenya-green)"></div>
            </div>
            <div class="hero-card-banner">
                <div class="banner-icon"><i class="fas fa-vote-yea"></i></div>
                <div class="banner-tagline">Your Kadi = Your Power</div>
                <div class="tri-underline"></div>
            </div>
            <div class="hero-card-body">
                <div class="hc-title">Why Register Today?</div>
                <div class="reason-list">
                    <div class="reason-item">
                        <span class="reason-num">01</span>
                        <div><div class="reason-title">Shape Kenya's Future</div><div class="reason-desc">Your vote decides who leads in 2027.</div></div>
                    </div>
                    <div class="reason-item">
                        <span class="reason-num">02</span>
                        <div><div class="reason-title">Amplify Gen Z Voice</div><div class="reason-desc">Young people have the numbers — now we need the power.</div></div>
                    </div>
                    <div class="reason-item">
                        <span class="reason-num">03</span>
                        <div><div class="reason-title">Be Part of History</div><div class="reason-desc">Join Kenya's biggest youth voter movement.</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="slider-dots" id="slider-dots"></div>
</section>

    <div class="section-stripe"></div>

    <!-- ANALYTICS -->
    <section id="analytics" class="analytics-section">
        <div class="section-inner">
            <div class="section-header">
                <div class="section-label">Real-Time Data</div>
                <h2 class="section-title">Live Registration Statistics</h2>
                <p class="section-sub">Real-time data showing how young Kenyans are taking charge of their future.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card green">
                    <div class="stat-num" id="live-confirmed-voters">{{ number_format($voterStats['confirmedVoters'] ?? 0) }}</div>
                    <div class="stat-label">Confirmed Voters</div>
                    <div class="stat-meta">Avg age: <span id="live-avg-age">{{ $voterStats['avgAge'] ?? '—' }}</span></div>
                    <div class="live-badge"><span class="live-dot" style="background:#00A86B"></span><span class="live-text">Live</span></div>
                </div>
                <div class="stat-card white">
                    <div class="stat-num" id="live-total-users">{{ number_format($totalUsers ?? 0) }}</div>
                    <div class="stat-label">Tuko Kadi Members</div>
                    <div class="stat-meta">&nbsp;</div>
                    <div class="live-badge"><span class="live-dot" style="background:#00A86B"></span><span class="live-text">Live</span></div>
                </div>
                <div class="stat-card red">
                    <div class="stat-num" id="live-total-messages">{{ number_format($totalMessages ?? 0) }}</div>
                    <div class="stat-label">Community Messages</div>
                    <div class="stat-meta">&nbsp;</div>
                    <div class="live-badge"><span class="live-dot" style="background:#ff5555"></span><span class="live-text">Live</span></div>
                </div>
                <div class="stat-card pink">
                    <div class="stat-num" id="live-stations">{{ number_format($stationsCount ?? 0) }}</div>
                    <div class="stat-label">Polling Stations</div>
                    <div class="stat-meta">&nbsp;</div>
                    <div class="live-badge"><span class="live-dot" style="background:#ff6eb4"></span><span class="live-text">Live</span></div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-card-title">Confirmed Voters by County</div>
                    <div class="chart-wrap"><canvas id="countyChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-card-title">Gender Distribution</div>
                    <div class="chart-wrap"><canvas id="genderChart"></canvas></div>
                </div>
            </div>

            <div class="county-list-card">
                <div class="chart-card-title">Top Counties by Voter Registration</div>
                <div class="county-table-scroll">
                    <table class="county-table">
                        <thead><tr><th>#</th><th>County</th><th>Registered</th></tr></thead>
                        <tbody id="county-table-body">
                            @foreach(($voterStats['byCounty'] ?? []) as $i => $county)
                            <tr>
                                <td><span class="county-rank">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</span></td>
                                <td>{{ $county->county }}</td>
                                <td><span class="county-badge">{{ number_format($county->count) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <div class="section-stripe" style="background:linear-gradient(90deg, var(--kenya-red) 0% 33.3%, #111 33.3% 66.6%, var(--kenya-green) 66.6% 100%)"></div>

    <!-- HOW IT WORKS -->
    <section id="how" class="how-section">
        <div class="section-inner">
            <div class="section-header">
                <div class="section-label">The Process</div>
                <h2 class="section-title">How to Get Your Voter's Card</h2>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-num">01</div>
                    <div class="step-title">Visit IEBC Centre</div>
                    <p class="step-desc">Go to your nearest IEBC registration point with your National ID.</p>
                </div>
                <div class="step-card">
                    <div class="step-num">02</div>
                    <div class="step-title">Register</div>
                    <p class="step-desc">Fill the form and verify your biometric details with the officials.</p>
                </div>
                <div class="step-card">
                    <div class="step-num">03</div>
                    <div class="step-title">Collect Your Kadi</div>
                    <p class="step-desc">Receive your voter's card and officially join the movement.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT -->
    <section id="about" class="about-section">
        <div class="about-inner">
            <div class="section-label" style="display:block;margin-bottom:16px">Who We Are</div>
            <h2 class="section-title" style="margin-bottom:28px">About My Leader Kenya Tuko Kadi Program</h2>
            <p class="about-text">Tuko Kadi is a non-partisan youth initiative dedicated to increasing voter registration among young Kenyans ahead of the 2027 General Election. We believe that when the youth actively participate in democracy, Kenya becomes stronger, more accountable, and truly representative of its future leaders.</p>
        </div>
    </section>

    <!-- CTA -->
    <div class="cta-section">
        <p class="cta-sub">This is not just registration.</p>
        <p class="cta-main">This is <em>our</em> Future.</p>
        <div class="cta-tag">
            <span class="t1">Niko Kadi.</span><span class="t2"> Je Wewe?</span><span class="t3"> 🇰🇪</span>
        </div>
        @guest
        <div style="margin-top:40px">
            <button onclick="openModal('register')" class="btn-hero-primary" style="display:inline-flex">
                <i class="fas fa-user-plus"></i> Join The Movement
            </button>
        </div>
        @endguest
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="footer-logo">TUKO KADI</div>
        <p class="footer-copy">&copy; {{ date('Y') }} Tuko Kadi. All rights reserved.</p>
        <div class="footer-links">
            <a href="{{ route('privacy') }}">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Contact Us</a>
        </div>
    </footer>

    <div class="flag-stripe"></div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
/* ── MODAL ── */
function openModal(tab) {
    document.getElementById('authModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    if (tab) switchTab(tab);
}
function closeModal() {
    document.getElementById('authModal').classList.remove('open');
    document.body.style.overflow = '';
}
function handleBackdropClick(e) {
    if (e.target === document.getElementById('authModal')) closeModal();
}
function switchTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(function(t){ t.classList.remove('active'); });
    document.querySelectorAll('.auth-form-panel').forEach(function(p){ p.classList.remove('active'); });
    document.getElementById('tab-'   + tab).classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
}
document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeModal(); });
document.addEventListener('DOMContentLoaded', function(){
    var authTab = new URLSearchParams(window.location.search).get('auth');
    if (authTab === 'login' || authTab === 'register') openModal(authTab);
});

@if ($errors->any())
document.addEventListener('DOMContentLoaded', function(){
    openModal('{{ old('_form_type', 'login') }}');
});
@endif

/* ── PASSWORD UTILITIES ── */
function togglePwd(id, btn) {
    var input = document.getElementById(id);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
function modalPwdStrength(val) {
    var fill  = document.getElementById('modal-pwd-fill');
    var label = document.getElementById('modal-pwd-label');
    if (!val) { fill.style.width='0%'; label.textContent=''; return; }
    var score = 0;
    if (val.length >= 8)           score++;
    if (val.length >= 12)          score++;
    if (/[A-Z]/.test(val))         score++;
    if (/[0-9]/.test(val))         score++;
    if (/[^A-Za-z0-9]/.test(val))  score++;
    var levels = [
        { pct:'20%', color:'#BB0000', text:'Very weak'   },
        { pct:'40%', color:'#e05a00', text:'Weak'        },
        { pct:'60%', color:'#d4a017', text:'Fair'        },
        { pct:'80%', color:'#00A86B', text:'Strong'      },
        { pct:'100%',color:'#00cc88', text:'Very strong' },
    ];
    var lvl = levels[Math.min(score, 4)];
    fill.style.width      = lvl.pct;
    fill.style.background = lvl.color;
    label.textContent     = lvl.text;
    label.style.color     = lvl.color;
}

let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const totalSlides = slides.length;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active'); // Hide all slides
        });
        slides[index].classList.add('active'); // Show the current slide
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides; // Cycle through slides
        showSlide(currentSlide);
    }

    // Show the first slide
    showSlide(currentSlide);

    // Change slide every 5 seconds
    setInterval(nextSlide, 5000);

/* ── HERO SLIDER ── */
(function(){
    var slides = document.querySelectorAll('.hero-slide');
    var dotsEl = document.getElementById('slider-dots');
    var cur = 0, total = slides.length, timer = null;
    if (!total) return;
    for (var i = 0; i < total; i++) {
        var d = document.createElement('button');
        d.className   = 'slider-dot' + (i === 0 ? ' active' : '');
        d.setAttribute('aria-label', 'Slide ' + (i+1));
        d.dataset.index = i;
        d.addEventListener('click', function(){ goTo(parseInt(this.dataset.index)); resetTimer(); });
        dotsEl.appendChild(d);
    }
    function goTo(idx) {
        slides[cur].classList.remove('active');
        dotsEl.querySelectorAll('.slider-dot')[cur].classList.remove('active');
        cur = (idx + total) % total;
        slides[cur].classList.add('active');
        dotsEl.querySelectorAll('.slider-dot')[cur].classList.add('active');
    }
    function resetTimer(){ clearInterval(timer); timer = setInterval(function(){ goTo(cur+1); }, 5000); }
    resetTimer();
    var hero = document.querySelector('.hero');
    if (hero) {
        hero.addEventListener('mouseenter', function(){ clearInterval(timer); });
        hero.addEventListener('mouseleave', resetTimer);
    }
})();

/* ── CHARTS ── */
document.addEventListener('DOMContentLoaded', function(){
    var countyChart = new Chart(document.getElementById('countyChart'), {
        type: 'bar',
        data: {
            labels: @json($countyLabels ?? []),
            datasets: [{
                label: 'Confirmed Voters',
                data:  @json($countyData ?? []),
                backgroundColor: function(ctx) {
                    var chart = ctx.chart, c = chart.ctx, ca = chart.chartArea;
                    if (!ca) return '#006600';
                    var g = c.createLinearGradient(0, ca.top, 0, ca.bottom);
                    g.addColorStop(0, '#BB0000'); g.addColorStop(1, '#006600');
                    return g;
                },
                borderRadius: 6, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(245,245,240,0.4)', font: { size:12, family:'Barlow' } }, border: { color: 'rgba(255,255,255,0.06)' } },
                x: { grid: { display: false },            ticks: { color: 'rgba(245,245,240,0.4)', font: { size:11, family:'Barlow' } }, border: { color: 'rgba(255,255,255,0.06)' } }
            }
        }
    });

    var genderChart = new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: ['Male','Female','Other / Not Specified'],
            datasets: [{ data: @json($genderData ?? [0,0,0]), backgroundColor: ['#BB0000','#00A86B','rgba(245,245,240,0.15)'], borderWidth: 3, borderColor: '#161616', hoverOffset: 8 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '68%',
            plugins: { legend: { position: 'bottom', labels: { color: 'rgba(245,245,240,0.5)', padding: 24, font: { size:13, family:'Barlow' }, usePointStyle: true, pointStyleWidth: 10 } } }
        }
    });

    if (window.__setChartRefs) window.__setChartRefs(countyChart, genderChart);
});

/* ── LIVE STATS ── */
(function(){
    function animateTo(el, newVal) {
        var start = parseInt(el.dataset.raw || el.textContent.replace(/,/g,'')) || 0;
        var end   = parseInt(newVal) || 0;
        if (start === end) return;
        el.dataset.raw = start;
        var dur = 900, startTs = performance.now();
        function step(now) {
            var p = Math.min((now - startTs) / dur, 1);
            var e = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(start + (end - start) * e).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
            else { el.textContent = end.toLocaleString(); el.dataset.raw = end; }
        }
        requestAnimationFrame(step);
    }
    var STAT_MAP = { 'live-confirmed-voters':'confirmedVoters', 'live-total-users':'totalUsers', 'live-total-messages':'totalMessages', 'live-stations':'stationsCount' };
    var ccRef = null, gcRef = null;
    window.__setChartRefs = function(c, g){ ccRef = c; gcRef = g; };

    function updateTable(labels, data) {
        var tbody = document.getElementById('county-table-body');
        if (!tbody || !labels || !data) return;
        tbody.innerHTML = labels.map(function(l, i){
            return '<tr><td><span class="county-rank">' + String(i+1).padStart(2,'0') + '</span></td><td>' + l + '</td><td><span class="county-badge">' + Number(data[i]||0).toLocaleString() + '</span></td></tr>';
        }).join('');
    }

    async function poll() {
        try {
            var res = await fetch('/api/stats/live', { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
            if (!res.ok) return;
            var data = await res.json();
            for (var id in STAT_MAP) { var el = document.getElementById(id); if (el && data[STAT_MAP[id]] !== undefined) animateTo(el, data[STAT_MAP[id]]); }
            var avgEl = document.getElementById('live-avg-age');
            if (avgEl && data.avgAge != null) avgEl.textContent = data.avgAge;
            if (ccRef && data.countyLabels && data.countyData) { ccRef.data.labels = data.countyLabels; ccRef.data.datasets[0].data = data.countyData; ccRef.update('none'); }
            if (gcRef && data.genderData)  { gcRef.data.datasets[0].data = data.genderData; gcRef.update('none'); }
            updateTable(data.countyLabels, data.countyData);
        } catch(e){}
    }
    setInterval(poll, 10000);
})();
</script>
@endpush