@if($isPolitical && ! empty($option['candidate']))
    @php $candidate = $option['candidate']; @endphp
    <span class="poll-option-photo" aria-hidden="true">
        @if($candidate['photo'])
            <img src="{{ $candidate['photo'] }}" alt="" loading="lazy" decoding="async">
        @else
            <span>{{ strtoupper(substr($candidate['name'], 0, 1)) }}</span>
        @endif
    </span>
    <span class="poll-option-body">
        <span class="poll-option-name">{{ $candidate['name'] }}</span>
        <span class="poll-option-meta">{{ $candidate['position'] }}, {{ $candidate['area'] }}</span>
        <span class="poll-option-party">{{ $candidate['party'] }}</span>
    </span>
@else
    <span class="poll-option-body is-plain">
        <span class="poll-option-name">{{ $option['label'] }}</span>
    </span>
@endif
