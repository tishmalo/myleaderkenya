@extends('layouts.landing')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap');

    /* Import variables and base styles from landing */
    :root {
        --kenya-red:    #BB0000;
        --kenya-black:  #111111;
        --kenya-white:  #F5F5F0;
        --gold:         #D4AF37;
        --green-bright: #00A86B;
        --red-bright:   #E8001A;
        --kenya-green:  #006600;
    }

    body {
        font-family: 'Barlow', sans-serif;
        background: var(--kenya-black);
        color: var(--kenya-white);
        overflow-x: hidden;
    }

    h1, h2, h3, h4, .display { font-family: 'Oswald', sans-serif; }

    .flag-stripe {
        height: 5px;
        background: linear-gradient(90deg,
            var(--kenya-black) 0% 33.3%,
            var(--kenya-red)   33.3% 66.6%,
            var(--kenya-green) 66.6% 100%);
    }

    /* NAV */
    nav.main-nav {
        background: rgba(10,10,10,0.97);
        border-bottom: 1px solid rgba(255,255,255,0.07);
        backdrop-filter: blur(16px);
        position: sticky; top: 0px; z-index: 100;
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

    /* FOOTER */
    footer { border-top: 1px solid rgba(255,255,255,0.06); background: #080808; padding: 52px 32px; text-align: center; }
    .footer-logo  { font-family: 'Oswald', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: 2px; color: rgba(255,255,255,0.2); margin-bottom: 20px; }
    .footer-copy  { font-size: 13px; color: rgba(255,255,255,0.2); }
    .footer-links { display: flex; justify-content: center; gap: 32px; margin-top: 16px; }
    .footer-links a { font-size: 13px; color: rgba(255,255,255,0.25); text-decoration: none; transition: color 0.2s; }
    .footer-links a:hover { color: rgba(255,255,255,0.6); }

    /* DONATE SECTION */
    .donate-container {
        max-width: 800px;
        margin: 60px auto 100px;
        padding: 0 32px;
    }

    .donate-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .donate-title {
        font-size: clamp(40px, 5vw, 56px);
        font-weight: 700;
        margin-bottom: 16px;
    }

    .donate-card {
        background: rgba(22,22,22,0.92);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px;
        padding: 40px;
        backdrop-filter: blur(12px);
    }

    .donate-why-title {
        font-family: 'Oswald', sans-serif;
        font-size: 24px;
        font-weight: 600;
        color: var(--kenya-red);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .donate-why-title::before {
        content: '';
        display: inline-block;
        width: 8px;
        height: 24px;
        background: var(--kenya-red);
        border-radius: 4px;
    }

    .donate-why-text {
        font-size: 17px;
        line-height: 1.7;
        color: rgba(245,245,240,0.8);
        margin-bottom: 30px;
    }

    .whatsapp-callout {
        background: rgba(0, 168, 107, 0.1);
        border: 1px solid rgba(0, 168, 107, 0.3);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 40px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .whatsapp-icon {
        font-size: 32px;
        color: var(--green-bright);
    }

    .whatsapp-text {
        font-size: 15px;
        color: rgba(245,245,240,0.9);
    }

    .whatsapp-link {
        color: var(--green-bright);
        font-weight: 600;
        text-decoration: none;
    }

    .whatsapp-link:hover {
        text-decoration: underline;
    }

    .payment-options {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding-top: 40px;
    }

    .payment-title {
        text-align: center;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 30px;
        color: var(--kenya-white);
    }

    .payment-methods {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .payment-method {
        background: #0f0f0f;
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        transition: transform 0.2s, border-color 0.2s;
    }

    .payment-method:hover {
        transform: translateY(-4px);
        border-color: var(--green-bright);
    }

    .payment-icon {
        font-size: 40px;
        margin-bottom: 16px;
        color: rgba(245,245,240,0.5);
    }

    .payment-method-name {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .payment-details {
        font-size: 14px;
        color: var(--green-bright);
        font-family: monospace;
        letter-spacing: 1px;
    }

    @media (max-width: 768px) {
        .payment-methods {
            grid-template-columns: 1fr;
        }
        .nav-links {
            display: none;
        }
    }
</style>

<div>
    <div class="flag-stripe"></div>

    <nav class="main-nav">
        <div class="nav-inner">
            <a href="/" class="brand">
                <div class="brand-logo">
                    <img src="{{ asset('images/myleader.png') }}" alt="My Leader Kenya Logo" class="logo-img">
                </div>
                <div class="brand-text">
                    <div class="brand-name">MY LEADER KENYA</div>
                    <div class="brand-sub">THE KENYA • WE WANT</div>
                </div>
            </a>

            <div class="nav-links">
                <a href="/#about">About Us</a>
                <a href="/#analytics">Live Stats</a>
                <a href="/#how">How It Works</a>
                <a href="{{ route('privacy') }}">Privacy</a>
                <a href="/donate" style="color:var(--green-bright)">Donate</a>
            </div>

            <div class="nav-cta">
                <a href="{{ route('dashboard') }}" class="btn-ghost">← Back</a>
            </div>
        </div>
    </nav>

    <div class="donate-container">
        <div class="donate-header">
            <h1 class="donate-title">Donate to Niko Kadi</h1>
        </div>

        <div class="donate-card">
            
            <div class="donate-why-title">Why?</div>
            <div class="donate-why-text">
                {{ $donateWhyText }}
            </div>

            <div class="whatsapp-callout">
                <div class="whatsapp-icon"><i class="fab fa-whatsapp"></i></div>
                <div class="whatsapp-text">
                    Join our active WhatsApp group for more: <br>
                    <a href="{{ $whatsappLink }}" target="_blank" class="whatsapp-link">{{ $whatsappLink }}</a>
                </div>
            </div>

            <div class="payment-options">
                <h3 class="payment-title">Donation Options</h3>
                <div class="payment-methods">
                    @forelse($paymentMethods as $method)
                        <div class="payment-method">
                            <div class="payment-icon">
                                @if($method->type === 'mpesa')
                                    <i class="fas fa-mobile-alt"></i>
                                @elseif($method->type === 'bank')
                                    <i class="fas fa-university"></i>
                                @else
                                    <i class="fas fa-wallet"></i>
                                @endif
                            </div>
                            <div class="payment-method-name">{{ $method->name }}</div>
                            <div class="payment-details">
                                @if($method->type === 'mpesa')
                                    @if($method->phone_number) Phone: {{ $method->phone_number }}<br> @endif
                                    @if($method->account_number) Paybill: {{ $method->account_number }}<br> @endif
                                    @if($method->account_name) Account: {{ $method->account_name }} @endif
                                @elseif($method->type === 'bank')
                                    @if($method->bank_name) Bank: {{ $method->bank_name }}<br> @endif
                                    @if($method->branch) Branch: {{ $method->branch }}<br> @endif
                                    @if($method->account_number) Acc: {{ $method->account_number }}<br> @endif
                                    @if($method->account_name) Name: {{ $method->account_name }} @endif
                                @else
                                    @if($method->account_number) No: {{ $method->account_number }}<br> @endif
                                    @if($method->account_name) Name: {{ $method->account_name }} @endif
                                @endif
                                
                                @if($method->instructions)
                                    <div class="mt-2 text-[11px] text-zinc-500 italic uppercase tracking-tighter leading-tight">
                                        {{ $method->instructions }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-10 text-zinc-600">
                            No payment options currently available.
                        </div>
                    @endforelse
                </div>
                <p style="text-align:center; font-size:12px; color:rgba(255,255,255,0.4); margin-top:24px;">
                    Thank you immensely for your support. Together, we can shape the future.
                </p>
            </div>
        </div>
    </div>

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
