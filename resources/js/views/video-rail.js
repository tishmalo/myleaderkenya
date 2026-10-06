/* Shared video-reel behaviour: click a thumbnail to swap in the player.
 * Bound once per page at document level (the rail component pushes this
 * file a single time no matter how many rails render). */
(function () {
    if (window.__videoRailBound) return;
    window.__videoRailBound = true;

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
})();
