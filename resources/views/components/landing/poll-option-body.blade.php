@props([
    'option',
    'plain' => false,
])

{{-- Renders one poll option. Every value arrives pre-computed from
     App\Support\PollPresenter: initial, party, meta and photo are already
     resolved, so this stays purely presentational. --}}
@if($option['is_political'])
    <span class="poll-option-photo" aria-hidden="true">
        @if($option['photo_url'])
            <img src="{{ $option['photo_url'] }}" alt="" loading="lazy" decoding="async">
        @else
            <span>{{ $option['initial'] }}</span>
        @endif
    </span>
    <span class="poll-option-body">
        <span class="poll-option-name">{{ $option['label'] }}</span>
        <span class="poll-option-meta">{{ $option['meta'] }}, {{ $option['area'] }}</span>
        <span class="poll-option-party">{{ $option['party'] }}</span>
    </span>
@else
    <span class="poll-option-body @if($plain) is-plain @endif">
        <span class="poll-option-name">{{ $option['label'] }}</span>
    </span>
@endif
