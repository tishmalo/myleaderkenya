@props(['item', 'type' => 'Political Party', 'url'])

<a href="{{ $url }}" class="party-card">
    @if($item->logo)
        <div class="party-card-img"><img src="{{ Storage::url($item->logo) }}" alt="{{ $item->name }}" loading="lazy"></div>
    @else
        <div class="party-card-no-img" style="--brand: {{ $item->brand_color ?: '#00A86B' }}"><i class="fas {{ $type === 'Coalition' ? 'fa-handshake' : 'fa-landmark' }}"></i></div>
    @endif
    <div class="party-card-body">
        <div class="party-card-kicker">{{ $type }}</div>
        <div class="party-card-title">{{ $item->name }}</div>
        @if($item->excerpt)<p class="party-card-excerpt">{{ $item->excerpt }}</p>@endif
        <div class="party-card-footer">View Profile <i class="fas fa-arrow-right"></i></div>
    </div>
</a>
