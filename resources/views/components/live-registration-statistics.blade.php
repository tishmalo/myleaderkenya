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
                <div class="stat-meta">Avg age: <span id="live-avg-age">{{ $voterStats['avgAge'] ?? '-' }}</span></div>
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
                            <td><span class="county-rank">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span></td>
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

<div id="landing-page-config"
     data-county-labels="{{ e(json_encode($countyLabels ?? [])) }}"
     data-county-data="{{ e(json_encode($countyData ?? [])) }}"
     data-gender-data="{{ e(json_encode($genderData ?? [0, 0, 0])) }}"
     data-auth-error-tab="{{ e($errors->any() ? old('_form_type', 'login') : '') }}"
     hidden></div>

