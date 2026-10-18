@php
    $viewer = auth()->user();
    $canOpenLinks = $viewer?->canViewMemberLinks() ?? false;
@endphp

@if(($resourceLinks ?? collect())->isNotEmpty())
    <section class="pages-links" aria-labelledby="pagesLinksTitle" data-pages-links>
        <div class="pages-links-head">
            <span class="bar"></span>
            <h2 class="pages-links-title" id="pagesLinksTitle">{{ $pagesLinksTitle ?? 'Pages &amp; Links' }}</h2>
            @unless($canOpenLinks)
                <span class="pages-links-gate-note"><i class="fas fa-lock" aria-hidden="true"></i> Registered voters only</span>
            @endunless
        </div>

        <div class="pages-links-grid">
            @foreach($resourceLinks as $resourceLink)
                @php
                    $audience = collect([
                        $resourceLink->candidate?->name,
                        $resourceLink->politicalParty?->abbreviation ?: $resourceLink->politicalParty?->name,
                        $resourceLink->ward?->name,
                        $resourceLink->constituency?->name,
                        $resourceLink->county?->name,
                        $resourceLink->isNational() ? 'Kenya' : null,
                    ])->filter()->unique()->take(3);
                @endphp

                @if($canOpenLinks)
                    <a class="pages-links-item" href="{{ $resourceLink->url }}" target="_blank" rel="noopener nofollow">
                        <i class="{{ $resourceLink->platform_icon }} pages-links-icon" aria-hidden="true"></i>
                        <div class="pages-links-body">
                            <span class="pages-links-title-txt">{{ $resourceLink->display_title }}</span>
                            <span class="pages-links-platform"><i class="{{ $resourceLink->platform_icon }}" aria-hidden="true"></i> {{ $resourceLink->platform_label }}</span>
                            @if($audience->isNotEmpty())
                                <span class="pages-links-audience">{!! $audience->map(fn ($part) => e($part))->implode(' &bull; ') !!}</span>
                            @endif
                            @if($resourceLink->comment)
                                <p class="pages-links-comment">{{ $resourceLink->comment }}</p>
                            @endif
                        </div>
                        @if(! is_null($resourceLink->followers))
                            <span class="pages-links-followers" title="Followers">
                                <i class="fas fa-users" aria-hidden="true"></i>
                                {{ number_format($resourceLink->followers) }}
                            </span>
                        @endif
                    </a>
                @else
                    {{-- The destination stays on the server until the visitor is a verified member. --}}
                    <button type="button" class="pages-links-item pages-links-item-locked" data-link-gate>
                        <i class="{{ $resourceLink->platform_icon }} pages-links-icon" aria-hidden="true"></i>
                        <div class="pages-links-body">
                            <span class="pages-links-title-txt">{{ $resourceLink->display_title }}</span>
                            <span class="pages-links-platform"><i class="{{ $resourceLink->platform_icon }}" aria-hidden="true"></i> {{ $resourceLink->platform_label }}</span>
                            @if($audience->isNotEmpty())
                                <span class="pages-links-audience">{!! $audience->map(fn ($part) => e($part))->implode(' &bull; ') !!}</span>
                            @endif
                            @if($resourceLink->comment)
                                <p class="pages-links-comment">{{ $resourceLink->comment }}</p>
                            @endif
                        </div>
                        <span class="pages-links-lock">
                            <i class="fas fa-lock" aria-hidden="true"></i> Login to view
                        </span>
                    </button>
                @endif
            @endforeach
        </div>

        @unless($canOpenLinks)
            <div class="pages-links-modal" data-link-gate-modal hidden>
                <div class="pages-links-modal-card" role="dialog" aria-modal="true" aria-labelledby="pagesLinksGateTitle" aria-describedby="pagesLinksGateCopy">
                    <span class="pages-links-modal-icon" aria-hidden="true"><i class="fas fa-lock"></i></span>
                    <h3 class="pages-links-modal-title" id="pagesLinksGateTitle">Registered voters only</h3>
                    <p class="pages-links-modal-copy" id="pagesLinksGateCopy">Only registered voters can view this page. Login or register to view this link</p>
                    <div class="pages-links-modal-actions">
                        <a class="pages-links-modal-btn primary" href="{{ route('login') }}">Login</a>
                        <a class="pages-links-modal-btn" href="{{ route('register') }}">Register</a>
                        <button type="button" class="pages-links-modal-btn ghost" data-link-gate-close>Close</button>
                    </div>
                </div>
            </div>

            <script>
            (function () {
                document.querySelectorAll('[data-pages-links]').forEach(function (root) {
                    if (root.dataset.gateReady === '1') return;
                    root.dataset.gateReady = '1';

                    var modal = root.querySelector('[data-link-gate-modal]');
                    if (!modal) return;

                    function open() {
                        modal.hidden = false;
                        document.body.style.overflow = 'hidden';
                        var first = modal.querySelector('.pages-links-modal-btn');
                        if (first) first.focus();
                    }

                    function close() {
                        modal.hidden = true;
                        document.body.style.overflow = '';
                    }

                    root.querySelectorAll('[data-link-gate]').forEach(function (trigger) {
                        trigger.addEventListener('click', open);
                    });

                    modal.querySelectorAll('[data-link-gate-close]').forEach(function (button) {
                        button.addEventListener('click', close);
                    });

                    modal.addEventListener('click', function (event) {
                        if (event.target === modal) close();
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && ! modal.hidden) close();
                    });
                });
            })();
            </script>
        @endunless
    </section>
@endif

<style>
    .pages-links { margin: 22px 0; }
    .pages-links-head { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 14px; }
    .pages-links-head .bar { display: block; width: 4px; height: 22px; border-radius: 2px; background: #059669; }
    .pages-links-title { margin: 0; font-size: 18px; font-weight: 800; color: #fff; }
    .pages-links-gate-note { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; background: rgba(5, 150, 105, .14); padding: 5px 11px; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #34d399; }
    .pages-links-grid { display: grid; gap: 12px; }
    .pages-links-item { display: flex; align-items: center; gap: 14px; border: 1px solid #27272a; border-radius: 14px; background: #111113; padding: 14px 16px; color: #fff; text-decoration: none; transition: border-color .18s ease, background .18s ease; }
    .pages-links-item:hover { border-color: #059669; background: #151517; }
    .pages-links-item-locked { width: 100%; font: inherit; text-align: left; cursor: pointer; }
    .pages-links-item-locked:hover { border-color: #10b981; }
    .pages-links-icon { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; border-radius: 12px; background: rgba(5, 150, 105, .16); color: #34d399; font-size: 19px; }
    .pages-links-body { display: flex; min-width: 0; flex: 1 1 auto; flex-direction: column; gap: 3px; }
    .pages-links-title-txt { font-size: 15px; font-weight: 800; }
    .pages-links-platform { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #a1a1aa; }
    .pages-links-audience { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #34d399; }
    .pages-links-comment { margin: 3px 0 0; font-size: 13px; line-height: 1.5; color: #a1a1aa; }
    .pages-links-followers { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 6px; border-radius: 999px; background: #202023; padding: 6px 11px; font-size: 12px; font-weight: 800; color: #a1a1aa; }

    .pages-links-lock { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 6px; border-radius: 999px; border: 1px solid rgba(16, 185, 129, .35); background: rgba(16, 185, 129, .1); padding: 6px 11px; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #6ee7b7; }
    .pages-links-modal { position: fixed; inset: 0; z-index: 100000; display: flex; align-items: center; justify-content: center; padding: 20px; background: rgba(0, 0, 0, .78); }
    .pages-links-modal[hidden] { display: none; }
    .pages-links-modal-card { width: 100%; max-width: 430px; border: 1px solid #27272a; border-radius: 22px; background: #141416; padding: 28px 24px; text-align: center; box-shadow: 0 30px 70px rgba(0, 0, 0, .55); }
    .pages-links-modal-icon { display: grid; width: 54px; height: 54px; margin: 0 auto 16px; place-items: center; border-radius: 16px; background: rgba(5, 150, 105, .16); color: #34d399; font-size: 22px; }
    .pages-links-modal-title { margin: 0 0 10px; font-size: 19px; font-weight: 800; color: #fff; }
    .pages-links-modal-copy { margin: 0 0 22px; font-size: 14px; line-height: 1.6; color: #a1a1aa; }
    .pages-links-modal-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
    .pages-links-modal-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 110px; border: 1px solid #3f3f46; border-radius: 12px; background: #1f1f23; padding: 12px 18px; font-size: 13px; font-weight: 800; color: #fff; text-decoration: none; cursor: pointer; transition: background .18s ease, border-color .18s ease; }
    .pages-links-modal-btn:hover { background: #27272b; border-color: #52525b; }
    .pages-links-modal-btn.primary { border-color: transparent; background: #059669; }
    .pages-links-modal-btn.primary:hover { background: #10b981; }
    .pages-links-modal-btn.ghost { background: transparent; color: #a1a1aa; }
</style>
