@php
    $videos = $videos ?? [
        ['id' => 'SNAMMJbnSFo', 'title' => 'Featured Video'],
        ['id' => 'M5arkEcnuy4', 'title' => 'Latest Update'],
    ];
    // Styling and click-to-play live in the shared video-rail layer
    // (resources/css|js/views/video-rail.js); pushed once no matter how
    // many rails a page renders. Pass ['compact' => true] for the
    // sidebar-sized variant.
@endphp
@pushOnce('styles')
    @vite('resources/css/views/video-rail.css')
@endPushOnce
@pushOnce('scripts')
    @vite('resources/js/views/video-rail.js')
@endPushOnce

<div class="page-rail{{ ! empty($compact) ? ' page-rail--compact' : '' }}" @if($railSticky ?? true) data-rail-sticky @endif>
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
