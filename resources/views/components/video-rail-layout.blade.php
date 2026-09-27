{{-- Wraps a page's card grid beside the sticky video rail.
     Usage:  <x-video-rail-layout> ...cards... </x-video-rail-layout>
     The rail column is fixed at 523px so each video card matches the campaign
     media card on the aspirant page exactly, which keeps the content column
     narrower than the video card. --}}
<div class="with-rail">
    <div class="with-rail-main">
        {{ $slot }}
    </div>

    <div class="with-rail-side">
        @include('components.video-rail')
    </div>
</div>

<style>
.with-rail {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 523px;
    gap: 28px;
    /* Not `align-items: start`: the side column has to stretch to the full row
       height, otherwise it collapses to the rail's own height and the sticky
       rail inside it has nowhere to travel. */
    align-items: stretch;
}
.with-rail-main { min-width: 0; }
.with-rail-side { min-width: 0; }

@media (max-width: 1180px) {
    .with-rail {
        grid-template-columns: minmax(0, 1fr);
        gap: 32px;
    }
    .with-rail-side .page-rail[data-rail-sticky] { position: static; }
    /* Stacked rail would run far past the fold, so pair the videos up. */
    .with-rail-side .rail-videos {
        flex-direction: row;
        flex-wrap: wrap;
    }
    .with-rail-side .rail-video { flex: 1 1 260px; }
}

@media (max-width: 640px) {
    .with-rail-side .rail-videos { flex-direction: column; }
    .with-rail-side .rail-video { flex: 1 1 auto; }
}
</style>
