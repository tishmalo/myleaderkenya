@props(['parties', 'routeName' => 'parties.show'])

<div class="member-party-list">
    @forelse($parties as $party)
        <a href="{{ route($routeName, $party->slug) }}" class="member-party-chip" style="--brand: {{ $party->brand_color ?: '#00A86B' }}">
            @if($party->logo)
                <img src="{{ Storage::url($party->logo) }}" alt="{{ $party->name }}">
            @else
                <span>{{ Str::upper(Str::substr($party->abbreviation ?? $party->name, 0, 2)) }}</span>
            @endif
            <strong>{{ $party->name }}</strong>
        </a>
    @empty
        <p class="member-party-empty">No entries listed yet.</p>
    @endforelse
</div>
