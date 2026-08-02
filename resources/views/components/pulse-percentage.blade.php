@props(['value'])
@php($percentage = is_numeric($value) ? (float) $value * 100 : 0.0)
<span data-sentiment-percentage>{{ number_format($percentage, 1) }}%</span>
