// Source Blade: resources/views/landing.blade.php
function readLandingJson(value, fallback) {
    if (!value) return fallback;

    try {
        return JSON.parse(value);
    } catch (error) {
        return fallback;
    }
}

function readLandingPageConfig() {
    var el = document.getElementById('landing-page-config');

    if (!el) {
        return { countyLabels: [], countyData: [], genderData: [0, 0, 0], authErrorTab: '' };
    }

    return {
        countyLabels: readLandingJson(el.dataset.countyLabels, []),
        countyData: readLandingJson(el.dataset.countyData, []),
        genderData: readLandingJson(el.dataset.genderData, [0, 0, 0]),
        authErrorTab: el.dataset.authErrorTab || ''
    };
}

window.LandingPageConfig = readLandingPageConfig();
/* ── MODAL ── */
window.openModal = function openModal(tab) {
    document.getElementById('authModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    if (tab) switchTab(tab);
}
window.closeModal = function closeModal() {
    document.getElementById('authModal').classList.remove('open');
    document.body.style.overflow = '';
}
window.handleBackdropClick = function handleBackdropClick(e) {
    if (e.target === document.getElementById('authModal')) closeModal();
}
window.switchTab = function switchTab(tab) {
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

document.addEventListener('DOMContentLoaded', function(){
    if (window.LandingPageConfig && window.LandingPageConfig.authErrorTab) {
        openModal(window.LandingPageConfig.authErrorTab);
    }
});

/* ── PASSWORD UTILITIES ── */
window.togglePwd = function togglePwd(id, btn) {
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
window.modalPwdStrength = function modalPwdStrength(val) {
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

/* Aspirant image carousel */
(function(){
    var carousel = document.querySelector('[data-aspirant-carousel]');
    if (!carousel) return;

    var endpoint = carousel.dataset.endpoint;
    var allAspirantsUrl = carousel.dataset.allAspirantsUrl || '/aspirants';
    var empty = carousel.querySelector('[data-aspirant-empty]');
    var slides = [];
    var pending = [];
    var current = 0;
    var nextPageUrl = endpoint;
    var isLoading = false;
    var timer = null;

    function createCard(aspirant) {
        var card = document.createElement('a');
        card.href = aspirant.url;
        card.className = 'aspirant-card';

        var image = document.createElement('img');
        image.src = aspirant.image_url;
        image.alt = aspirant.name;
        image.loading = 'lazy';

        var body = document.createElement('div');
        body.className = 'aspirant-card-body';

        var name = document.createElement('div');
        name.className = 'aspirant-name';
        name.textContent = aspirant.name;

        var position = document.createElement('div');
        position.className = 'aspirant-position';
        position.textContent = aspirant.position || 'Aspirant';

        body.appendChild(name);
        body.appendChild(position);

        var metaParts = [aspirant.area, aspirant.party].filter(Boolean);
        if (metaParts.length) {
            var meta = document.createElement('div');
            meta.className = 'aspirant-meta';
            meta.textContent = metaParts.join(' - ');
            body.appendChild(meta);
        }
        card.appendChild(image);
        card.appendChild(body);

        return card;
    }

    function createAllAspirantsLink() {
        var link = document.createElement('a');
        link.href = allAspirantsUrl;
        link.className = 'aspirant-view-all';
        link.innerHTML = 'View All Aspirants <i class="fas fa-arrow-right"></i>';
        return link;
    }

    function appendSlide(items) {
        var slide = document.createElement('div');
        slide.className = 'aspirant-slide' + (slides.length === 0 ? ' active' : '');
        slide.dataset.aspirantSlide = '';

        var grid = document.createElement('div');
        grid.className = 'aspirant-slide-grid';
        items.forEach(function (aspirant) {
            grid.appendChild(createCard(aspirant));
        });

        slide.appendChild(grid);
        slide.appendChild(createAllAspirantsLink());
        carousel.appendChild(slide);
        slides.push(slide);
    }

    function appendAspirants(items) {
        if (!items.length && slides.length === 0 && empty) {
            empty.querySelector('.banner-tagline').textContent = 'Your Kadi = Your Power';
            return;
        }

        if (empty) empty.remove();
        pending = pending.concat(items);

        while (pending.length >= 2) {
            appendSlide(pending.splice(0, 2));
        }

        if (!nextPageUrl && pending.length > 0) {
            appendSlide(pending.splice(0, 2));
        }

        startTimer();
    }

    function loadNextPage() {
        if (!nextPageUrl || isLoading) return;

        isLoading = true;
        fetch(nextPageUrl, { headers: { 'Accept': 'application/json' } })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                nextPageUrl = data.next_page_url;
                appendAspirants(data.data || []);
            })
            .catch(function () {
                if (empty) empty.querySelector('.banner-tagline').textContent = 'Featured aspirants unavailable';
            })
            .finally(function () {
                isLoading = false;
            });
    }

    function showSlide(index) {
        if (slides.length <= 1) return;

        slides[current].classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');

        if (nextPageUrl && current >= slides.length - 3) {
            loadNextPage();
        }
    }

    function startTimer() {
        if (timer || slides.length <= 1) return;

        timer = setInterval(function(){
            showSlide(current + 1);
        }, 10000);
    }

    loadNextPage();
})();
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
            labels: window.LandingPageConfig.countyLabels || [],
            datasets: [{
                label: 'Confirmed Voters',
                data:  window.LandingPageConfig.countyData || [],
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
            datasets: [{ data: window.LandingPageConfig.genderData || [0,0,0], backgroundColor: ['#BB0000','#00A86B','rgba(245,245,240,0.15)'], borderWidth: 3, borderColor: '#161616', hoverOffset: 8 }]
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


