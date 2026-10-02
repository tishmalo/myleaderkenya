@if(($resourceLinks ?? collect())->isNotEmpty())
    <section class="pages-links" aria-labelledby="pagesLinksTitle">
        <div class="pages-links-head">
            <span class="bar"></span>
            <h2 class="pages-links-title" id="pagesLinksTitle">{{ $pagesLinksTitle ?? 'Pages &amp; Links' }}</h2>
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
                    ])->filter()->unique()->take(3);
                @endphp
                <a class="pages-links-item" href="{{ $resourceLink->url }}" target="_blank" rel="noopener nofollow">
                    <i class="{{ $resourceLink->platform_icon }} pages-links-icon" aria-hidden="true"></i>
                    <div class="pages-links-body">
                        <span class="pages-links-title-txt">{{ $resourceLink->display_title }}</span>
                        <span class="pages-links-platform"><i class="{{ $resourceLink->platform_icon }}" aria-hidden="true"></i> {{ $resourceLink->platform_label }}</span>
                        @if($audience->isNotEmpty())
                            <span class="pages-links-audience">{{ $audience->implode(' &bull; ') }}</span>
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
            @endforeach
        </div>
    </section>
@endif

<style>
    .pages-links { margin: 22px 0; }
    .pages-links-head { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .pages-links-head .bar { display: block; width: 4px; height: 22px; border-radius: 2px; background: #059669; }
    .pages-links-title { margin: 0; font-size: 18px; font-weight: 800; color: #fff; }
    .pages-links-grid { display: grid; gap: 12px; }
    .pages-links-item { display: flex; align-items: center; gap: 14px; border: 1px solid #27272a; border-radius: 14px; background: #111113; padding: 14px 16px; color: #fff; text-decoration: none; transition: border-color .18s ease, background .18s ease; }
    .pages-links-item:hover { border-color: #059669; background: #151517; }
    .pages-links-icon { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; border-radius: 12px; background: rgba(5, 150, 105, .16); color: #34d399; font-size: 19px; }
    .pages-links-body { display: flex; min-width: 0; flex: 1 1 auto; flex-direction: column; gap: 3px; }
    .pages-links-title-txt { font-size: 15px; font-weight: 800; }
    .pages-links-platform { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #a1a1aa; }
    .pages-links-audience { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #34d399; }
    .pages-links-comment { margin: 3px 0 0; font-size: 13px; line-height: 1.5; color: #a1a1aa; }
    .pages-links-followers { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 6px; border-radius: 999px; background: #202023; padding: 6px 11px; font-size: 12px; font-weight: 800; color: #a1a1aa; }
</style>
