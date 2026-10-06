{{-- Wraps a page's card grid beside the sticky video rail.
     Styling lives in the shared video-rail layer
     (resources/css/views/video-rail.css), pushed once by the rail itself.
     Usage:  <x-video-rail-layout> ...cards... </x-video-rail-layout>
             <x-video-rail-layout variant="majority"> ...cards... </x-video-rail-layout>
     The "majority" variant gives the grid ~70% and the sidebar ~30% on
     desktop; anything else keeps the default fixed-rail split. --}}
@props(['variant' => null])

<div class="with-rail{{ $variant === 'majority' ? ' with-rail--majority' : '' }}">
    <div class="with-rail-main">
        {{ $slot }}
    </div>

    <div class="with-rail-side">
        @include('components.video-rail')
    </div>
</div>
