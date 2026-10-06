{{-- Wraps a page's card grid beside the sticky video rail.
     Styling lives in the shared video-rail layer
     (resources/css/views/video-rail.css), pushed once by the rail itself.
     Usage:  <x-video-rail-layout> ...cards... </x-video-rail-layout> --}}

<div class="with-rail">
    <div class="with-rail-main">
        {{ $slot }}
    </div>

    <div class="with-rail-side">
        @include('components.video-rail')
    </div>
</div>
