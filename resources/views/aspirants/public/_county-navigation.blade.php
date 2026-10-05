@if($countyNavigation->isNotEmpty())
    @php
        $selectedPosition = $positions->firstWhere('id', (int) request('position'));
        $countyNavigationQuery = request()->only(['position', 'political_party', 'country']);
    @endphp
    <section class="county-navigation" aria-labelledby="other-counties-title">
        <div class="county-navigation-head">
            <h2 id="other-counties-title">{{ $selectedPosition?->name ?? 'Aspirants' }} in other counties</h2>
            <p>Choose another county without returning to the main menu.</p>
        </div>
        <div class="location-card-grid">
            @foreach($countyNavigation as $group)
                <a href="{{ route('aspirants.public', array_merge($countyNavigationQuery, ['county' => $group['filter_value']])) }}" class="location-card">
                    @if(!empty($group['image_url']))
                        <img src="{{ $group['image_url'] }}" alt="{{ $group['label'] }}">
                    @else
                        <div class="location-card-placeholder">{{ substr($group['label'], 0, 1) }}</div>
                    @endif
                    <span class="location-card-label">{{ $group['label'] }}</span>
                    <span class="location-card-meta">{{ $group['total'] }} aspirant{{ $group['total'] !== 1 ? 's' : '' }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
