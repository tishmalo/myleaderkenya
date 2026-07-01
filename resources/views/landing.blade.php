@extends('layouts.landing')

@section('content')
@push('styles')
    @vite('resources/css/views/landing.css')
@endpush


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
                    <!-- <button class="auth-tab"         id="tab-register" onclick="switchTab('register')">Join Now</button> -->
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
                            <label>Email Address</label>
                            <div class="auth-field-wrap">
                                <input type="email" name="email" placeholder="admin@nikokadi.top"
                                       value="{{ old('email') }}" required autocomplete="username" autofocus>
                                <span class="auth-field-icon"><i class="fas fa-envelope"></i></span>
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
                <div class="aspirant-carousel" data-aspirant-carousel data-endpoint="{{ route('landing.featured-aspirants') }}?per_page=20" data-all-aspirants-url="{{ route('aspirants.public') }}">
                    <div class="aspirant-carousel-empty" data-aspirant-empty>
                        <div>
                            <div class="banner-icon"><i class="fas fa-vote-yea"></i></div>
                            <div class="banner-tagline">Loading featured aspirants</div>
                            <div class="tri-underline"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero-card-body">
                <div class="hc-title">Latest News Updates</div>
                @if(($latestBlogs ?? collect())->isNotEmpty())
                    <div class="latest-blog-list">
                        @foreach($latestBlogs as $blog)
                            <a href="{{ route('news.public.show', $blog->slug) }}" class="latest-blog-item">
                                <div class="latest-blog-image">
                                    @if($blog->featured_image)
                                        <img src="{{ Storage::url($blog->featured_image) }}" alt="{{ $blog->title }}">
                                    @else
                                        <i class="fas fa-newspaper"></i>
                                    @endif
                                </div>
                                <div class="latest-blog-copy">
                                    <div class="latest-blog-date">{{ optional($blog->published_at)->format('M d, Y') ?? $blog->created_at->format('M d, Y') }}</div>
                                    <div class="latest-blog-title">{{ $blog->title }}</div>
                                    <div class="latest-blog-excerpt">{{ \Illuminate\Support\Str::limit($blog->excerpt ?: strip_tags($blog->content), 92) }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="reason-desc">Latest news updates will appear here once published.</div>
                @endif
                <a href="{{ route('news.public') }}" class="latest-blog-more">
                    More News <i class="fas fa-arrow-right"></i>
                </a>
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

    <!-- FEATURED ASPIRANTS -->
    <section class="featured-aspirants-section">
        <div class="section-inner">
            <div class="section-header">
                <div class="section-label">Featured Aspirants</div>
                <h2 class="section-title">Aspirants From President to MCA</h2>
            </div>

            @php
                $aspirantGroups = collect($homepageAspirantGroups ?? []);
                $hasAspirants = $aspirantGroups->sum(fn ($group) => $group['candidates']->count()) > 0;
            @endphp

            @if($hasAspirants)
                <div class="aspirants-table-grid">
                    @foreach($aspirantGroups as $group)
                        <div class="aspirants-table-card">
                            <div class="aspirants-table-head">
                                <div class="aspirants-table-title">{{ $group['label'] }}</div>
                                <a href="{{ route('aspirants.public', ['position' => $group['position']]) }}" class="aspirants-view-more">
                                    View more <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                            @if($group['candidates']->isNotEmpty())
                                <div class="aspirants-table-wrap">
                                    <table class="aspirants-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Party</th>
                                                <th>County</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group['candidates'] as $candidate)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('aspirants.show', $candidate) }}" class="aspirants-name-link">
                                                            {{ $candidate->name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $candidate->politicalParty->abbreviation ?? $candidate->politicalParty->name ?? '-' }}</td>
                                                    <td>{{ $candidate->county ?? $candidate->country ?? '-' }}</td>
                                                    <td>
                                                        @if($candidate->featured)
                                                            <span class="aspirants-featured-pill">Featured</span>
                                                        @else
                                                            Newest
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="aspirants-empty">No {{ strtolower($group['label']) }} aspirants yet.</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="aspirants-empty">No aspirants are available yet.</div>
            @endif
        </div>
    </section>
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

<div id="landing-page-config"
     data-county-labels="{{ e(json_encode($countyLabels ?? [])) }}"
     data-county-data="{{ e(json_encode($countyData ?? [])) }}"
     data-gender-data="{{ e(json_encode($genderData ?? [0, 0, 0])) }}"
     data-auth-error-tab="{{ e($errors->any() ? old('_form_type', 'login') : '') }}"
     hidden></div>

@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@vite('resources/js/views/landing.js')
@endpush





