{{-- Wraps a page's card grid beside the sticky video rail.
     Styling lives in the shared video-rail layer
     (resources/css/views/video-rail.css), pushed once by the rail itself.
     Usage:  <x-video-rail-layout> ...cards... </x-video-rail-layout>
             <x-video-rail-layout variant="majority"> ...cards... </x-video-rail-layout>
     The "majority" variant gives the grid ~70% and the sidebar ~30% on
     desktop with full-width sidebar videos; anything else keeps the
     default fixed-rail split with the compact rail. --}}
@props(['variant' => null])

<div class="with-rail{{ $variant === 'majority' ? ' with-rail--majority' : '' }}">
    <div class="with-rail-main">
        {{ $slot }}
    </div>

    <div class="with-rail-side">
        {{-- The majority layout was approved with full-width sidebar videos;
             every other layout takes the compact default. --}}
        @include('components.video-rail', ['compact' => $variant !== 'majority'])
    </div>
</div>
