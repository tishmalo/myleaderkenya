@php
    $videos = $videos ?? [
        ['id' => 'SNAMMJbnSFo', 'title' => 'Watch the latest from MyLeader'],
        ['id' => 'M5arkEcnuy4', 'title' => 'Featured video'],
    ];
@endphp

@if(count($videos))
<section class="vs-showcase" aria-label="Featured videos">
    <div class="vs-inner">
        <div class="vs-head">
            <div class="vs-head-bar"></div>
            <span class="vs-head-label">Watch</span>
        </div>

        <div class="vs-grid">
            @foreach($videos as $video)
                <div class="vs-card" data-vs-video data-video-id="{{ $video['id'] }}">
                    <div class="vs-thumb" data-vs-thumb>
                        <img src="https://i.ytimg.com/vi/{{ $video['id'] }}/hqdefault.jpg"
                             alt="{{ $video['title'] }}" loading="lazy">
                        <button type="button" class="vs-play" data-vs-play
                                aria-label="Play {{ $video['title'] }}">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                    <div class="vs-caption">{{ $video['title'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<style>
.vs-showcase {
    max-width: 1100px; margin: 0 auto;
    padding: 56px 32px 72px;
    font-family: 'Barlow', sans-serif;
}
.vs-head {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 22px;
}
.vs-head-bar {
    width: 3px; height: 18px; border-radius: 2px;
    background: #BB0000;
}
.vs-head-label {
    font-family: 'Oswald', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    color: rgba(255,120,120,0.85);
}
.vs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
}
.vs-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 18px; overflow: hidden;
    transition: border-color .2s, transform .2s;
}
.vs-card:hover { border-color: rgba(0,168,107,0.28); transform: translateY(-2px); }
.vs-thumb {
    position: relative;
    aspect-ratio: 16/9;
    background: #0d0d0d;
}
.vs-thumb img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
}
.vs-thumb::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(10,10,10,0.7), rgba(10,10,10,0.05) 60%, transparent);
    pointer-events: none;
}
.vs-play {
    position: absolute; inset: 0; margin: auto;
    width: 62px; height: 62px;
    border: none; border-radius: 50%;
    background: rgba(187,0,0,0.92);
    color: #fff; font-size: 20px;
    cursor: pointer; z-index: 2;
    display: flex; align-items: center; justify-content: center;
    padding-left: 4px;
    transition: background .2s, transform .2s;
}
.vs-play:hover { background: #00A86B; transform: scale(1.07); }
.vs-caption {
    padding: 14px 18px 16px;
    font-family: 'Oswald', sans-serif;
    font-size: 14px; font-weight: 600;
    letter-spacing: .3px;
    color: rgba(245,245,240,0.75);
}
.vs-embed {
    position: relative;
    aspect-ratio: 16/9;
    background: #000;
}
.vs-embed iframe {
    width: 100%; height: 100%;
    display: block; border: none;
}
@media (max-width: 768px) {
    .vs-showcase { padding: 36px 16px 48px; }
    .vs-grid { grid-template-columns: minmax(0, 1fr); gap: 18px; }
}
</style>

<script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-vs-play]');
        if (!button) return;

        var card = button.closest('[data-vs-video]');
        var thumb = card && card.querySelector('[data-vs-thumb]');
        var id = card && card.getAttribute('data-video-id');
        if (!thumb || !id) return;

        var embed = document.createElement('div');
        embed.className = 'vs-embed';
        embed.innerHTML = '<iframe src="https://www.youtube-nocookie.com/embed/' + id +
            '?autoplay=1" title="Video player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';

        thumb.replaceWith(embed);
    });
</script>
