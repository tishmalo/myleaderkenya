@extends('layouts.landing')

@section('title', $pageData['content']['meta_title'] ?: ($pageData['content']['title'] . ' - Tuko Kadi'))
@section('meta_description', $pageData['content']['meta_description'] ?: $pageData['content']['excerpt'])

@section('content')
<style>
body { font-family:'Barlow',sans-serif; background:#0a0a0a; color:var(--kenya-white); }
.fp-hero { padding:110px 32px 72px; background:linear-gradient(135deg, rgba(187,0,0,0.18), rgba(0,102,0,0.14)); border-bottom:1px solid rgba(255,255,255,0.07); }
.fp-inner { max-width:1120px; margin:0 auto; }
.fp-kicker { color:#00a651; text-transform:uppercase; letter-spacing:.12em; font-weight:700; font-size:13px; }
.fp-title { font-family:'Oswald',sans-serif; font-size:clamp(42px,7vw,78px); line-height:.95; margin:14px 0 22px; }
.fp-excerpt { max-width:760px; color:rgba(245,245,240,.68); font-size:20px; line-height:1.6; }
.fp-body { padding:72px 32px; }
.fp-content { max-width:820px; color:rgba(245,245,240,.76); font-size:18px; line-height:1.85; white-space:pre-line; }
.fp-cta { display:inline-flex; margin-top:34px; align-items:center; gap:10px; background:#bb0000; color:white; padding:14px 22px; border-radius:8px; text-decoration:none; font-weight:700; }
.analytics-section { background:#0d0d0d; padding:80px 0 100px; }
.section-inner { max-width:1280px; margin:0 auto; padding:0 32px; }
.section-header { text-align:center; margin-bottom:64px; }
.section-label { display:inline-block; font-size:10px; font-weight:700; letter-spacing:4px; text-transform:uppercase; color:var(--kenya-red); margin-bottom:16px; }
.section-title { font-family:'Oswald',sans-serif; font-size:clamp(36px,4vw,52px); font-weight:700; line-height:1.05; }
.section-sub { font-size:17px; color:rgba(245,245,240,0.45); margin-top:12px; }
.stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:48px; }
.stat-card { border-radius:16px; padding:32px 24px; border:1px solid rgba(255,255,255,0.06); position:relative; overflow:hidden; text-align:center; }
.stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
.stat-card.green { background:rgba(0,102,0,0.12); } .stat-card.green::before { background:var(--green-bright); }
.stat-card.white { background:rgba(255,255,255,0.04); } .stat-card.white::before { background:rgba(255,255,255,0.3); }
.stat-card.red { background:rgba(187,0,0,0.12); } .stat-card.red::before { background:var(--kenya-red); }
.stat-card.pink { background:rgba(232,0,100,0.1); } .stat-card.pink::before { background:#e80064; }
.stat-num { font-family:'Oswald',sans-serif; font-size:50px; font-weight:700; line-height:1; }
.stat-card.green .stat-num { color:var(--green-bright); }
.stat-card.white .stat-num { color:var(--kenya-white); }
.stat-card.red .stat-num { color:#ff5555; }
.stat-card.pink .stat-num { color:#ff6eb4; }
.stat-label { font-size:13px; color:rgba(245,245,240,0.45); margin-top:8px; letter-spacing:0.5px; }
.stat-meta { font-size:11px; color:rgba(245,245,240,0.3); margin-top:6px; letter-spacing:0.5px; min-height:16px; }
.stat-meta span { color:var(--green-bright); }
.live-badge { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:10px; }
.live-dot { width:7px; height:7px; border-radius:50%; display:inline-block; animation:pulse 1.5s infinite; }
.live-text { font-size:10px; letter-spacing:2px; color:rgba(245,245,240,0.35); text-transform:uppercase; font-weight:700; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(1.5)} }
.charts-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
.chart-card { background:#161616; border:1px solid rgba(255,255,255,0.07); border-radius:20px; padding:32px; }
.chart-card-title { font-family:'Oswald',sans-serif; font-size:20px; font-weight:600; margin-bottom:24px; letter-spacing:0.5px; }
.chart-wrap { height:340px; }
.county-list-card { background:#161616; border:1px solid rgba(255,255,255,0.07); border-radius:20px; padding:32px; margin-top:24px; }
.county-table { width:100%; border-collapse:collapse; }
.county-table th { font-size:10px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:rgba(245,245,240,0.3); padding:0 0 12px; text-align:left; }
.county-table th:last-child { text-align:right; }
.county-table td { padding:12px 0; border-top:1px solid rgba(255,255,255,0.04); font-size:14px; }
.county-table td:last-child { text-align:right; }
.county-badge { display:inline-block; background:rgba(0,168,107,0.12); color:var(--green-bright); border-radius:20px; padding:3px 12px; font-size:12px; font-weight:600; }
.county-rank { font-family:'Oswald',sans-serif; font-size:18px; font-weight:700; color:rgba(245,245,240,0.15); margin-right:16px; }
.county-table-scroll { overflow:visible; }
@media(max-width:900px){ .stats-grid{grid-template-columns:1fr 1fr}.charts-grid{grid-template-columns:1fr} }
@media(max-width:760px){ .fp-hero{padding:88px 20px 54px}.fp-body,.analytics-section{padding:52px 20px}.section-inner{padding:0}.stats-grid{grid-template-columns:1fr}.fp-title{font-size:44px}.chart-card,.county-list-card{padding:22px}.chart-wrap{height:300px} }
</style>

@include('components.frontend-nav')

<section class="fp-hero">
    <div class="fp-inner">
        <div class="fp-kicker">{{ $pageData['content']['title'] }}</div>
        <h1 class="fp-title">{{ $pageData['content']['hero_title'] }}</h1>
        @if($pageData['content']['excerpt'])
            <p class="fp-excerpt">{{ $pageData['content']['excerpt'] }}</p>
        @endif
    </div>
</section>

@if($pageData['key'] === 'live-stats')
    <section class="analytics-section">
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

    <div id="live-stats-page-config"
         data-county-labels="{{ e(json_encode($countyLabels ?? [])) }}"
         data-county-data="{{ e(json_encode($countyData ?? [])) }}"
         data-gender-data="{{ e(json_encode($genderData ?? [0, 0, 0])) }}"
         hidden></div>
@else
    <section class="fp-body">
        <div class="fp-inner">
            <div class="fp-content">{{ $pageData['content']['content'] }}</div>

            @if($pageData['content']['cta_label'] && $pageData['content']['cta_url'])
                @php($ctaUrl = Str::startsWith($pageData['content']['cta_url'], ['http://', 'https://', 'mailto:', 'tel:', '#']) ? $pageData['content']['cta_url'] : url($pageData['content']['cta_url']))
                <a href="{{ $ctaUrl }}" class="fp-cta">{{ $pageData['content']['cta_label'] }} <i class="fas fa-arrow-right"></i></a>
            @endif
        </div>
    </section>
@endif
@endsection

@if($pageData['key'] === 'live-stats')
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var configEl = document.getElementById('live-stats-page-config');
    if (!configEl || typeof Chart === 'undefined') return;

    function readJson(name, fallback) {
        try { return JSON.parse(configEl.dataset[name] || ''); } catch (e) { return fallback; }
    }

    var countyLabels = readJson('countyLabels', []);
    var countyData = readJson('countyData', []);
    var genderData = readJson('genderData', [0, 0, 0]);
    var countyCanvas = document.getElementById('countyChart');
    var genderCanvas = document.getElementById('genderChart');
    if (!countyCanvas || !genderCanvas) return;

    var countyChart = new Chart(countyCanvas, {
        type: 'bar',
        data: {
            labels: countyLabels,
            datasets: [{
                label: 'Confirmed Voters',
                data: countyData,
                backgroundColor: function (ctx) {
                    var chart = ctx.chart, c = chart.ctx, ca = chart.chartArea;
                    if (!ca) return '#006600';
                    var g = c.createLinearGradient(0, ca.top, 0, ca.bottom);
                    g.addColorStop(0, '#BB0000');
                    g.addColorStop(1, '#006600');
                    return g;
                },
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(245,245,240,0.4)', font: { size: 12, family: 'Barlow' } }, border: { color: 'rgba(255,255,255,0.06)' } },
                x: { grid: { display: false }, ticks: { color: 'rgba(245,245,240,0.4)', font: { size: 11, family: 'Barlow' } }, border: { color: 'rgba(255,255,255,0.06)' } }
            }
        }
    });

    var genderChart = new Chart(genderCanvas, {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female', 'Other'],
            datasets: [{
                data: genderData,
                backgroundColor: ['#006600', '#BB0000', '#f5f5f0'],
                borderColor: '#161616',
                borderWidth: 4,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: { legend: { position: 'bottom', labels: { color: 'rgba(245,245,240,0.5)', padding: 24, font: { size: 13, family: 'Barlow' }, usePointStyle: true, pointStyleWidth: 10 } } }
        }
    });

    function animateTo(el, newVal) {
        var start = parseInt(el.dataset.raw || el.textContent.replace(/,/g, '')) || 0;
        var end = parseInt(newVal) || 0;
        if (start === end) return;
        el.dataset.raw = start;
        var duration = 900;
        var startTs = performance.now();
        function step(now) {
            var p = Math.min((now - startTs) / duration, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(start + (end - start) * eased).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
            else { el.textContent = end.toLocaleString(); el.dataset.raw = end; }
        }
        requestAnimationFrame(step);
    }

    function updateTable(labels, data) {
        var tbody = document.getElementById('county-table-body');
        if (!tbody || !labels || !data) return;
        tbody.innerHTML = labels.map(function (label, i) {
            return '<tr><td><span class="county-rank">' + String(i + 1).padStart(2, '0') + '</span></td><td>' + label + '</td><td><span class="county-badge">' + Number(data[i] || 0).toLocaleString() + '</span></td></tr>';
        }).join('');
    }

    var statMap = { 'live-confirmed-voters': 'confirmedVoters', 'live-total-users': 'totalUsers', 'live-total-messages': 'totalMessages', 'live-stations': 'stationsCount' };
    async function poll() {
        try {
            var res = await fetch('/api/stats/live', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            var data = await res.json();
            Object.keys(statMap).forEach(function (id) {
                var el = document.getElementById(id);
                if (el && data[statMap[id]] !== undefined) animateTo(el, data[statMap[id]]);
            });
            var avgEl = document.getElementById('live-avg-age');
            if (avgEl && data.avgAge != null) avgEl.textContent = data.avgAge;
            if (data.countyLabels && data.countyData) {
                countyChart.data.labels = data.countyLabels;
                countyChart.data.datasets[0].data = data.countyData;
                countyChart.update('none');
                updateTable(data.countyLabels, data.countyData);
            }
            if (data.genderData) {
                genderChart.data.datasets[0].data = data.genderData;
                genderChart.update('none');
            }
        } catch (e) {}
    }
    setInterval(poll, 10000);
});
</script>
@endpush
@endif
