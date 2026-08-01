@props(['score'])
@php
    $numericScore = is_numeric($score) ? (float) $score : null;
    $scoreClass = $numericScore === null || $numericScore == 0.0
        ? 'text-zinc-200'
        : ($numericScore > 0 ? 'text-emerald-300' : 'text-red-300');
    $formattedScore = $numericScore === null
        ? 'N/A'
        : ($numericScore > 0 ? '+' : '').number_format($numericScore, 2);
@endphp
<div class="mt-2 text-2xl font-semibold {{ $scoreClass }}" data-pulse-score-sign="{{ $numericScore === null || $numericScore == 0.0 ? 'neutral' : ($numericScore > 0 ? 'positive' : 'negative') }}">{{ $formattedScore }}</div>
<div class="mt-2 text-xs text-zinc-500">Range: -100 negative, 0 neutral, +100 positive</div>
