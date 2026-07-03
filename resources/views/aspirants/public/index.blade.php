@extends('layouts.landing')
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap');

:root {
    --kenya-red:    #BB0000;
    --kenya-black:  #111111;
    --kenya-white:  #F5F5F0;
    --green-bright: #00A86B;
    --kenya-green:  #006600;
    --gold:         #D4AF37;
}

* { box-sizing: border-box; }

body {
    font-family: 'Barlow', sans-serif;
    background: #0a0a0a;
    color: var(--kenya-white);
}

h1, h2, h3, h4 { font-family: 'Oswald', sans-serif; }

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
/* ── PAGE HERO ── */
.asp-hero {
    position: relative;
    padding: 80px 32px 60px;
    text-align: center;
    overflow: hidden;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.asp-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 70% 80% at 20% 50%, rgba(187,0,0,0.14) 0%, transparent 60%),
        radial-gradient(ellipse 70% 80% at 80% 50%, rgba(0,102,0,0.14) 0%, transparent 60%);
    pointer-events: none;
}
.asp-hero-stripe {
    height: 4px;
    background: linear-gradient(90deg, var(--kenya-green) 33%, #1a1a1a 33% 66%, var(--kenya-red) 66%);
    margin-bottom: 56px;
}
.asp-hero-eyebrow {
    display: inline-flex; align-items: center; gap: 10px;
    border: 1px solid rgba(187,0,0,0.4);
    background: rgba(187,0,0,0.08);
    padding: 6px 18px; border-radius: 40px;
    font-size: 11px; font-weight: 700; letter-spacing: 3px;
    color: #ff6666; text-transform: uppercase; margin-bottom: 24px;
}
.asp-hero-eyebrow .dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--kenya-red);
    animation: pulse 1.6s infinite;
}
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(1.6)} }

.asp-hero h1 {
    font-size: clamp(44px, 6vw, 76px);
    font-weight: 700; line-height: 0.95;
    letter-spacing: -1px; margin-bottom: 16px;
}
.asp-hero h1 em {
    font-style: normal;
    background: linear-gradient(135deg, var(--green-bright), var(--kenya-green));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.asp-hero p {
    font-size: 18px; color: rgba(245,245,240,0.45);
    max-width: 460px; margin: 0 auto;
}

/* ── FILTER BAR ── */
.filter-bar-wrap {
    max-width: 1280px; margin: 0 auto;
    padding: 0 32px; position: relative; z-index: 10;
    margin-top: -28px; margin-bottom: 48px;
}
.filter-bar {
    background: #161616;
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 16px;
    padding: 16px 20px;
    display: flex; align-items: center; gap: 12px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.filter-bar-label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 3px; text-transform: uppercase;
    color: rgba(245,245,240,0.25);
    white-space: nowrap; padding-right: 4px;
    border-right: 1px solid rgba(255,255,255,0.07);
    margin-right: 4px;
    flex-shrink: 0;
}
.filter-divider {
    width: 1px; height: 32px;
    background: rgba(255,255,255,0.07);
    flex-shrink: 0;
}
.filter-input-wrap {
    position: relative; flex: 2; min-width: 0;
}
.filter-select-wrap {
    position: relative; flex: 1; min-width: 0;
}
.filter-icon {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.2); font-size: 13px;
    pointer-events: none; transition: color 0.2s;
}
.filter-input-wrap:focus-within .filter-icon,
.filter-select-wrap:focus-within .filter-icon { color: var(--green-bright); }

.filter-bar input,
.filter-bar select {
    width: 100%;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    padding: 11px 14px 11px 38px;
    color: white; font-size: 14px;
    font-family: 'Barlow', sans-serif;
    transition: border-color 0.2s, background 0.2s;
    appearance: none; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
.filter-bar input:focus,
.filter-bar select:focus {
    outline: none;
    border-color: var(--green-bright);
    background: rgba(0,168,107,0.06);
}
.filter-bar input::placeholder { color: rgba(255,255,255,0.2); }
.filter-bar select option { background: #1c1c1c; }

.filter-btn {
    flex-shrink: 0;
    padding: 11px 28px;
    font-family: 'Oswald', sans-serif;
    font-size: 13px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    background: var(--kenya-red); color: white;
    border: none; border-radius: 10px; cursor: pointer;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    white-space: nowrap;
    box-shadow: 0 0 20px rgba(187,0,0,0.3);
    display: flex; align-items: center; gap: 8px;
}
.filter-btn:hover {
    background: #cc0000; transform: translateY(-1px);
    box-shadow: 0 0 32px rgba(187,0,0,0.5);
}

/* ── RESULTS META ── */
.results-meta {
    max-width: 1280px; margin: 0 auto 28px;
    padding: 0 32px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px;
}
.results-count {
    font-size: 13px; color: rgba(245,245,240,0.35);
    letter-spacing: 0.5px;
}
.results-count strong { color: var(--green-bright); font-weight: 700; }
.results-tri {
    height: 2px; flex: 1;
    background: linear-gradient(90deg, rgba(0,168,107,0.3), transparent);
}

/* ── GRID ── */
.asp-grid {
    max-width: 1280px; margin: 0 auto;
    padding: 0 32px 80px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}
.asp-county-heading {
    grid-column: 1 / -1;
    display: flex; align-items: center; gap: 14px;
    margin-top: 10px;
}
.asp-county-heading:first-child { margin-top: 0; }
.asp-county-heading-title {
    font-family: 'Oswald', sans-serif;
    font-size: 18px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--kenya-white);
    white-space: nowrap;
}
.asp-county-heading-count {
    font-size: 12px; font-weight: 700;
    color: var(--green-bright);
}
.asp-county-heading-line {
    height: 1px; flex: 1;
    background: linear-gradient(90deg, rgba(0,168,107,0.45), rgba(187,0,0,0.2), transparent);
}

/* ── CANDIDATE CARD ── */
.asp-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    transition: border-color 0.3s, transform 0.3s, box-shadow 0.3s;
    display: flex; flex-direction: column;
}
.asp-card:hover {
    border-color: rgba(0,168,107,0.35);
    transform: translateY(-4px);
    box-shadow: 0 24px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(0,168,107,0.15);
}

/* Photo area */
.asp-card-photo {
    position: relative; height: 220px; overflow: hidden;
}
.asp-card-photo img {
    width: 100%; height: 100%;
    object-fit: cover; object-position: top center;
    transition: transform 0.5s ease;
}
.asp-card:hover .asp-card-photo img { transform: scale(1.05); }

.asp-card-photo-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, rgba(187,0,0,0.2) 0%, rgba(0,102,0,0.2) 100%);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 8px;
}
.asp-card-photo-placeholder .initials {
    font-family: 'Oswald', sans-serif;
    font-size: 52px; font-weight: 700;
    color: rgba(255,255,255,0.12);
    line-height: 1;
}

/* Gradient overlay on photo */
.asp-card-photo-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, #141414 0%, rgba(20,20,20,0.3) 50%, transparent 100%);
}

/* Position badge on photo */
.asp-card-position-badge {
    position: absolute; top: 14px; left: 14px;
    background: rgba(0,0,0,0.65);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 1px; text-transform: uppercase;
    color: rgba(245,245,240,0.7);
}

/* County flag tag on photo */
.asp-card-county-tag {
    position: absolute; bottom: 14px; right: 14px;
    display: flex; align-items: center; gap: 6px;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(0,168,107,0.25);
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11px; color: var(--green-bright); font-weight: 600;
}

/* Card body */
.asp-card-body {
    padding: 20px 22px 22px;
    flex: 1; display: flex; flex-direction: column;
}
.asp-card-name {
    font-family: 'Oswald', sans-serif;
    font-size: 22px; font-weight: 700;
    line-height: 1.1; margin-bottom: 4px;
    color: var(--kenya-white);
}
.asp-card-nick {
    font-size: 13px; color: rgba(0,168,107,0.8);
    font-style: italic; margin-bottom: 14px;
}
.asp-card-location {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; color: rgba(245,245,240,0.35);
    margin-bottom: 18px;
}
.asp-card-location i { font-size: 10px; }

/* Bottom accent line */
.asp-card-divider {
    height: 1px;
    background: linear-gradient(90deg, rgba(0,168,107,0.2), rgba(187,0,0,0.2), transparent);
    margin-bottom: 18px;
}

.asp-card-action {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 10px;
    text-decoration: none;
    transition: background 0.2s, border-color 0.2s;
    margin-top: auto;
}
.asp-card-action:hover {
    background: rgba(0,168,107,0.08);
    border-color: rgba(0,168,107,0.3);
}
.asp-card-action-text {
    font-family: 'Oswald', sans-serif;
    font-size: 13px; font-weight: 600;
    letter-spacing: 1px; text-transform: uppercase;
    color: rgba(245,245,240,0.7);
}
.asp-card-action:hover .asp-card-action-text { color: var(--green-bright); }
.asp-card-action-arrow {
    width: 28px; height: 28px;
    background: var(--kenya-red);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: white;
    transition: background 0.2s, transform 0.2s;
}
.asp-card:hover .asp-card-action-arrow {
    background: var(--green-bright);
    transform: translateX(2px);
}

/* ── EMPTY STATE ── */
.asp-empty {
    grid-column: 1 / -1;
    text-align: center; padding: 80px 20px;
}
.asp-empty-icon {
    font-size: 56px; margin-bottom: 20px; opacity: 0.3;
}
.asp-empty h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 28px; color: rgba(245,245,240,0.4);
    margin-bottom: 8px;
}
.asp-empty p { font-size: 15px; color: rgba(245,245,240,0.2); }

/* ── PAGINATION ── */
.asp-pagination {
    max-width: 1280px; margin: 0 auto;
    padding: 0 32px 80px;
}
.asp-pagination .pagination {
    display: flex; justify-content: center;
    gap: 8px; list-style: none; flex-wrap: wrap;
}
.asp-pagination .page-item .page-link {
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 40px;
    background: #161616;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    color: rgba(245,245,240,0.5);
    font-size: 14px; text-decoration: none;
    transition: all 0.2s;
}
.asp-pagination .page-item.active .page-link {
    background: var(--kenya-red);
    border-color: var(--kenya-red);
    color: white;
}
.asp-pagination .page-item .page-link:hover:not(.active) {
    border-color: var(--green-bright);
    color: var(--green-bright);
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .filter-bar {
        flex-wrap: wrap;
        gap: 10px;
    }
    .filter-bar-label, .filter-divider { display: none; }
    .filter-input-wrap, .filter-select-wrap { flex: 1 1 calc(50% - 5px); }
    .filter-btn { width: 100%; justify-content: center; }
    .asp-grid { grid-template-columns: 1fr; padding: 0 16px 60px; }
    .filter-bar-wrap { padding: 0 16px; }
    .results-meta { padding: 0 16px; }
}
@media (max-width: 480px) {
    .filter-input-wrap, .filter-select-wrap { flex: 1 1 100%; }
}
</style>

<!-- HERO -->
    <div class="asp-hero-stripe"></div>
    @include('components.frontend-nav')
<div class="asp-hero">
    
    <div class="asp-hero-eyebrow"><span class="dot"></span> General Election 2027</div>
    <h1>Kenya's <em>Aspirants</em></h1>
    <p>Meet the men and women seeking to lead Kenya into its next chapter.</p>
</div>

<!-- FILTER BAR -->

<div class="filter-bar-wrap">
    <form method="GET">
        <div class="filter-bar" data-aspirant-filter>
            <span class="filter-bar-label">Filter</span>

            <div class="filter-input-wrap">
                <span class="filter-icon"><i class="fas fa-search"></i></span>
                <input type="text" name="candidate" value="{{ request('candidate', request('search')) }}"
                       placeholder="Search candidate by name or nickname...">
            </div>

            <div class="filter-divider"></div>

            <div class="filter-select-wrap">
                <span class="filter-icon"><i class="fas fa-briefcase"></i></span>
                <select name="position" data-position-filter>
                    <option value="" data-position-key="">All Positions</option>
                    @foreach($positions as $pos)
                        @php
                            $positionKey = strtolower(str_replace([' ', '_'], '-', $pos->name));
                        @endphp
                        <option value="{{ $pos->id }}" data-position-key="{{ $positionKey }}" {{ request('position') == $pos->id ? 'selected' : '' }}>
                            {{ $pos->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-divider" data-location-divider></div>

            <div class="filter-select-wrap" data-location-filter="country">
                <span class="filter-icon"><i class="fas fa-globe-africa"></i></span>
                <select name="country">
                    <option value="">Country</option>
                    @foreach(($countries ?? collect(['Kenya'])) as $country)
                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-select-wrap" data-location-filter="county">
                <span class="filter-icon"><i class="fas fa-map-marker-alt"></i></span>
                <select name="county" data-county-filter>
                    <option value="">County</option>
                    @foreach($counties as $county)
                        <option value="{{ $county }}" {{ request('county') == $county ? 'selected' : '' }}>{{ $county }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-select-wrap" data-location-filter="constituency">
                <span class="filter-icon"><i class="fas fa-location-arrow"></i></span>
                <select name="constituency" data-constituency-filter data-selected="{{ request('constituency') }}">
                    <option value="">Constituency</option>
                    @foreach($constituencies as $constituency)
                        <option value="{{ $constituency }}" {{ request('constituency') == $constituency ? 'selected' : '' }}>{{ $constituency }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-select-wrap" data-location-filter="ward">
                <span class="filter-icon"><i class="fas fa-map-pin"></i></span>
                <select name="ward" data-ward-filter data-selected="{{ request('ward') }}">
                    <option value="">Ward</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward }}" {{ request('ward') == $ward ? 'selected' : '' }}>{{ $ward }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-divider"></div>

            <button type="submit" class="filter-btn">
                <i class="fas fa-bolt"></i> Search
            </button>
        </div>
    </form>
</div>

<!-- RESULTS META -->
@php
    $candidateFilter = request('candidate') ?: request('search');
@endphp

<div class="results-meta">
    <div class="results-count">
        Showing <strong>{{ $candidates->total() }}</strong> aspirant{{ $candidates->total() != 1 ? 's' : '' }}
        @if($candidateFilter)
            matching <strong>{{ $candidateFilter }}</strong>
        @endif
        @if(request('position'))
            for selected position
        @endif
        @if(request('country'))
            in <strong>{{ request('country') }}</strong>
        @endif
        @if(request('county'))
            in <strong>{{ request('county') }}</strong>
        @endif
        @if(request('constituency'))
            / <strong>{{ request('constituency') }}</strong>
        @endif
        @if(request('ward'))
            / <strong>{{ request('ward') }}</strong>
        @endif
    </div>
    <div class="results-tri"></div>
</div>

<!-- GRID -->
@php
    $candidateGroups = $candidates->getCollection()->groupBy(function ($candidate) {
        $positionName = strtolower(optional($candidate->position)->name ?? '');

        if (str_contains($positionName, 'president')) {
            return 'National';
        }

        return $candidate->county ?: 'County not specified';
    });
@endphp

<div class="asp-grid">
    @forelse($candidateGroups as $countyName => $countyCandidates)
        <div class="asp-county-heading">
            <span class="asp-county-heading-title">{{ $countyName }}</span>
            <span class="asp-county-heading-count">{{ $countyCandidates->count() }}</span>
            <span class="asp-county-heading-line"></span>
        </div>

        @foreach($countyCandidates as $candidate)
            <div class="asp-card">
                <!-- Photo -->
                <div class="asp-card-photo">
                    @if($candidate->profile_picture)
                        <img src="{{ Storage::url($candidate->profile_picture) }}"
                             alt="{{ $candidate->name }}" loading="lazy">
                    @else
                        <div class="asp-card-photo-placeholder">
                            <span class="initials">
                                {{ strtoupper(substr($candidate->name, 0, 1)) }}{{ strtoupper(substr(strrchr($candidate->name, ' ') ?: '', 1, 1)) }}
                            </span>
                        </div>
                    @endif
                    <div class="asp-card-photo-overlay"></div>

                    @if($candidate->position)
                        <div class="asp-card-position-badge">{{ $candidate->position->name }}</div>
                    @endif

                    @if($candidate->county)
                        <div class="asp-card-county-tag">
                            <i class="fas fa-map-marker-alt" style="font-size:9px"></i>
                            {{ $candidate->county }}
                        </div>
                    @endif
                </div>

                <!-- Body -->
                <div class="asp-card-body">
                    <div class="asp-card-name">{{ $candidate->name }}</div>

                    @if($candidate->nick_name)
                        <div class="asp-card-nick">"{{ $candidate->nick_name }}"</div>
                    @endif

                    @if($candidate->constituency)
                        <div class="asp-card-location">
                            <i class="fas fa-circle" style="font-size:4px;color:var(--green-bright)"></i>
                            {{ $candidate->constituency }}
                            @if($candidate->ward)
                                &nbsp;&middot;&nbsp; {{ $candidate->ward }}
                            @endif
                        </div>
                    @endif

                    <div class="asp-card-divider"></div>

                    <a href="{{ route('aspirants.show', $candidate) }}" class="asp-card-action">
                        <span class="asp-card-action-text">View Profile</span>
                        <span class="asp-card-action-arrow"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
            </div>
        @endforeach
    @empty
        <div class="asp-empty">
            <div class="asp-empty-icon"></div>
            <h3>No aspirants found</h3>
            <p>Try adjusting your filters or check back soon.</p>
        </div>
    @endforelse
</div>

<!-- PAGINATION -->
@if($candidates->hasPages())
    <div class="asp-pagination">
        {{ $candidates->links() }}
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('[data-aspirant-filter]');
    if (!root) return;

    var positionSelect = root.querySelector('[data-position-filter]');
    var countySelect = root.querySelector('[data-county-filter]');
    var constituencySelect = root.querySelector('[data-constituency-filter]');
    var wardSelect = root.querySelector('[data-ward-filter]');
    var locationGroups = root.querySelectorAll('[data-location-filter]');

    function selectedPositionKey() {
        var option = positionSelect.options[positionSelect.selectedIndex];
        return (option && option.dataset.positionKey || '').toLowerCase();
    }

    function positionScope(key) {
        if (!key || key.indexOf('president') !== -1) return ['country'];
        if (key.indexOf('mca') !== -1 || key.indexOf('county-assembly') !== -1) return ['county', 'constituency', 'ward'];
        if (key === 'mp' || key.indexOf('parliament') !== -1) return ['county', 'constituency'];
        if (key.indexOf('governor') !== -1 || key.indexOf('senator') !== -1 || key.indexOf('woman') !== -1 || key.indexOf('women') !== -1) return ['county'];
        return ['country'];
    }

    function setSelectOptions(select, values, placeholder, selectedValue) {
        select.innerHTML = '';
        select.appendChild(new Option(placeholder, ''));
        values.forEach(function (item) {
            var value = typeof item === 'string' ? item : (item.name || item.value || '');
            if (!value) return;
            var option = new Option(value, value);
            if (value === selectedValue) option.selected = true;
            select.appendChild(option);
        });
    }

    function fetchOptions(url, select, placeholder, selectedValue) {
        fetch(url, { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.ok ? response.json() : []; })
            .then(function (data) { setSelectOptions(select, Array.isArray(data) ? data : [], placeholder, selectedValue || ''); })
            .catch(function () { setSelectOptions(select, [], placeholder, ''); });
    }

    function applyScope() {
        var allowed = positionScope(selectedPositionKey());
        locationGroups.forEach(function (group) {
            var visible = allowed.indexOf(group.dataset.locationFilter) !== -1;
            group.style.display = visible ? '' : 'none';
            var select = group.querySelector('select');
            if (select) {
                select.disabled = !visible;
                if (!visible) select.value = '';
            }
        });
    }

    positionSelect.addEventListener('change', function () {
        countySelect.value = '';
        setSelectOptions(constituencySelect, [], 'Constituency', '');
        setSelectOptions(wardSelect, [], 'Ward', '');
        applyScope();
    });

    countySelect.addEventListener('change', function () {
        setSelectOptions(wardSelect, [], 'Ward', '');
        if (!countySelect.value) {
            setSelectOptions(constituencySelect, [], 'Constituency', '');
            return;
        }
        fetchOptions('/api/locations/constituencies/by-county?county=' + encodeURIComponent(countySelect.value), constituencySelect, 'Constituency', '');
    });

    constituencySelect.addEventListener('change', function () {
        if (!constituencySelect.value) {
            setSelectOptions(wardSelect, [], 'Ward', '');
            return;
        }
        fetchOptions('/api/locations/wards/by-constituency?constituency=' + encodeURIComponent(constituencySelect.value), wardSelect, 'Ward', '');
    });

    applyScope();
});
</script>
@endsection