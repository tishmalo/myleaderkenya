@php
    $videos = $videos ?? [
        ['id' => 'SNAMMJbnSFo', 'title' => 'Featured Video'],
        ['id' => 'M5arkEcnuy4', 'title' => 'Latest Update'],
    ];
@endphp

<div class="page-rail" @if($railSticky ?? true) data-rail-sticky @endif>
    <div class="rail-videos">
        @foreach($videos as $video)
            <div class="rail-video" data-vs-video data-video-id="{{ $video['id'] }}">
                <div class="rail-video-label">
                    <i class="fa-brands fa-youtube"></i> {{ $video['title'] }}
                </div>
                <button type="button" class="rail-video-thumb" data-vs-play
                        aria-label="Play {{ $video['title'] }}">
                    <img src="https://i.ytimg.com/vi/{{ $video['id'] }}/hqdefault.jpg"
                         alt="{{ $video['title'] }}" loading="lazy">
                    <span class="rail-video-play"><i class="fas fa-play"></i></span>
                </button>
            </div>
        @endforeach
    </div>

    {{ $extra ?? '' }}
</div>

<style>
.page-rail {
    display: flex;
    flex-direction: column;
    gap: 22px;
    min-width: 0;
}
/* No max-height or overflow here: capping the rail turns it into its own
   scroll container, which breaks the sticky pin and collapses the cards
   below the videos. The rail is free to be taller than the viewport;
   sticky releases it at the end of its containing block. */
.page-rail[data-rail-sticky] {
    position: sticky;
    top: 88px;
    align-self: start;
}
.page-rail > * { flex-shrink: 0; }

.rail-videos { display: flex; flex-direction: column; gap: 18px; }

/* Matches the campaign media card on the aspirant page. */
.rail-video {
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 13px;
    background: #111;
}
.rail-video-label {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 40px;
    padding: 9px 12px;
    color: #fff;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: .3px;
}
.rail-video-label i.fa-youtube { color: #ff3434; }
.rail-video-thumb {
    position: relative;
    display: block;
    width: 100%;
    aspect-ratio: 16/9;
    border: 0;
    padding: 0;
    background: #000;
    cursor: pointer;
}
.rail-video-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.rail-video-play {
    position: absolute;
    inset: 0;
    margin: auto;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding-left: 4px;
    background: rgba(187,0,0,.92);
    color: #fff;
    font-size: 18px;
    transition: background .2s, transform .2s;
}
.rail-video-thumb:hover .rail-video-play {
    background: #00A86B;
    transform: scale(1.07);
}
.rail-video-frame {
    display: block;
    width: 100%;
    aspect-ratio: 16/9;
    border: 0;
    background: #000;
}
</style>

<script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-vs-play]');
        if (!button) return;

        var card = button.closest('[data-vs-video]');
        var id = card && card.getAttribute('data-video-id');
        if (!card || !id) return;

        var frame = document.createElement('iframe');
        frame.className = 'rail-video-frame';
        frame.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1';
        frame.title = 'Video player';
        frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        frame.allowFullscreen = true;
        button.replaceWith(frame);
    });
</script>
