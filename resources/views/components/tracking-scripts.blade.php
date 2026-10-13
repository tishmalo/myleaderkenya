@props(['area' => 'public'])

{{-- Analytics, Tag Manager, AdSense, pixel and custom head code managed from
     Settings -> Tracking & Ads. Renders nothing when the area is off. --}}
@inject('trackingScripts', 'App\Services\Web\TrackingScriptService')
{!! $trackingScripts->headMarkup($area) !!}
