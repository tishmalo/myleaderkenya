function readLiveStatsJson(value, fallback) {
    if (!value) return fallback;

    try {
        return JSON.parse(value);
    } catch (error) {
        return fallback;
    }
}

function readLiveStatsConfig() {
    var el = document.getElementById('landing-page-config');

    if (!el) {
        return { countyLabels: [], countyData: [], genderData: [0, 0, 0] };
    }

    return {
        countyLabels: readLiveStatsJson(el.dataset.countyLabels, []),
        countyData: readLiveStatsJson(el.dataset.countyData, []),
        genderData: readLiveStatsJson(el.dataset.genderData, [0, 0, 0])
    };
}

window.LandingPageConfig = readLiveStatsConfig();

document.addEventListener('DOMContentLoaded', function () {
    var countyCanvas = document.getElementById('countyChart');
    var genderCanvas = document.getElementById('genderChart');
    var countyChart = null;
    var genderChart = null;

    if (countyCanvas && window.Chart) {
        countyChart = new Chart(countyCanvas, {
            type: 'bar',
            data: {
                labels: window.LandingPageConfig.countyLabels || [],
                datasets: [{
                    label: 'Confirmed Voters',
                    data: window.LandingPageConfig.countyData || [],
                    backgroundColor: function (ctx) {
                        var chart = ctx.chart;
                        var c = chart.ctx;
                        var ca = chart.chartArea;
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
    }

    if (genderCanvas && window.Chart) {
        genderChart = new Chart(genderCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female', 'Other / Not Specified'],
                datasets: [{ data: window.LandingPageConfig.genderData || [0, 0, 0], backgroundColor: ['#BB0000', '#00A86B', 'rgba(245,245,240,0.15)'], borderWidth: 3, borderColor: '#161616', hoverOffset: 8 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: { legend: { position: 'bottom', labels: { color: 'rgba(245,245,240,0.5)', padding: 24, font: { size: 13, family: 'Barlow' }, usePointStyle: true, pointStyleWidth: 10 } } }
            }
        });
    }

    function animateTo(el, newVal) {
        var start = parseInt(el.dataset.raw || el.textContent.replace(/,/g, '')) || 0;
        var end = parseInt(newVal) || 0;
        if (start === end) return;
        el.dataset.raw = start;
        var dur = 900;
        var startTs = performance.now();

        function step(now) {
            var p = Math.min((now - startTs) / dur, 1);
            var e = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(start + (end - start) * e).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
            else {
                el.textContent = end.toLocaleString();
                el.dataset.raw = end;
            }
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

    async function poll() {
        try {
            var res = await fetch('/api/stats/live', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            var data = await res.json();
            var statMap = { 'live-confirmed-voters': 'confirmedVoters', 'live-total-users': 'totalUsers', 'live-total-messages': 'totalMessages', 'live-stations': 'stationsCount' };

            for (var id in statMap) {
                var el = document.getElementById(id);
                if (el && data[statMap[id]] !== undefined) animateTo(el, data[statMap[id]]);
            }

            var avgEl = document.getElementById('live-avg-age');
            if (avgEl && data.avgAge != null) avgEl.textContent = data.avgAge;
            if (countyChart && data.countyLabels && data.countyData) {
                countyChart.data.labels = data.countyLabels;
                countyChart.data.datasets[0].data = data.countyData;
                countyChart.update('none');
            }
            if (genderChart && data.genderData) {
                genderChart.data.datasets[0].data = data.genderData;
                genderChart.update('none');
            }
            updateTable(data.countyLabels, data.countyData);
        } catch (error) {}
    }

    setInterval(poll, 10000);
});
