{{-- Position-grouped aspirant sections, shared by the party and county pages.
     Expects $groups as [['position' => Position, 'candidates' => paginator]],
     plus $headingId, $title, $total and $gridClass (the consumer defines the
     grid itself). Colours use var() fallbacks so the block renders
     identically on pages that do not define the Kenya palette. --}}
<section class="position-groups" aria-labelledby="{{ $headingId }}">
    <div class="position-groups-head">
        <div>
            <h2 id="{{ $headingId }}">{{ $title }}</h2>
            @if(!empty($subtitle ?? null))<p>{{ $subtitle }}</p>@endif
        </div>
        @if(($total ?? 0) > 0)
            <div class="position-groups-count">
                {{ number_format($total) }}
                {{ Str::plural('aspirant', $total) }}
            </div>
        @endif
    </div>
    @forelse($groups as $group)
        <section class="position-group" aria-labelledby="position-{{ $group['position']->id }}">
            <div class="position-group-head">
                <h3 class="position-group-title" id="position-{{ $group['position']->id }}">
                    {{ $group['position']->name }}
                </h3>
                <span class="position-group-count">
                    {{ number_format($group['candidates']->total()) }}
                    {{ Str::plural('aspirant', $group['candidates']->total()) }}
                </span>
            </div>

            <div class="{{ $gridClass }}">
                @foreach($group['candidates'] as $candidate)
                    @include('aspirants.public._card', ['candidate' => $candidate])
                @endforeach
            </div>

            @if($group['candidates']->hasPages())
                <div class="position-groups-pagination">
                    {{ $group['candidates']->links() }}
                </div>
            @endif
        </section>
    @empty
        <div class="position-groups-empty">
            <i class="fas fa-users"></i>
            <h3>{{ $emptyTitle ?? 'No approved aspirants yet' }}</h3>
            <p>{{ $emptyText ?? 'Approved aspirants will appear here.' }}</p>
        </div>
    @endforelse
</section>

<style>
    .position-groups { margin-top: 32px; padding: 36px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.08); background: #141414; }
    .position-groups-head { display: flex; align-items: end; justify-content: space-between; gap: 18px; margin-bottom: 26px; }
    .position-groups-head h2 { margin: 0 0 6px; color: white; font-size: 32px; }
    .position-groups-head p { margin: 0; color: rgba(245,245,240,0.45); }
    .position-groups-count { color: var(--green-bright, #00A86B); font-size: 12px; font-weight: 800; letter-spacing: 1.3px; text-transform: uppercase; white-space: nowrap; }
    .position-group + .position-group { margin-top: 38px; padding-top: 34px; border-top: 1px solid rgba(255,255,255,.08); }
    .position-group-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
    .position-group-title { display: flex; align-items: center; gap: 12px; margin: 0; color: white; font-size: 25px; }
    .position-group-title::before { content: ''; width: 4px; height: 28px; border-radius: 999px; background: linear-gradient(to bottom, var(--kenya-red, #BB0000), var(--kenya-green, #006600)); }
    .position-group-count { color: rgba(245,245,240,.45); font-size: 11px; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase; white-space: nowrap; }
    .position-groups-empty { padding: 54px 20px; text-align: center; color: rgba(245,245,240,.4); }
    .position-groups-empty i { display: block; margin-bottom: 16px; color: rgba(0,168,107,.5); font-size: 38px; }
    .position-groups-empty h3 { margin: 0 0 8px; color: rgba(245,245,240,.65); font-size: 24px; }
    .position-groups-empty p { margin: 0; }
    .position-groups-pagination { margin-top: 30px; display: flex; justify-content: center; }
    @media (max-width: 768px) { .position-groups { padding: 24px; } .position-groups-head { align-items: start; flex-direction: column; } }
    @media (max-width: 520px) { .position-group-head { align-items: flex-start; flex-direction: column; } }
</style>
