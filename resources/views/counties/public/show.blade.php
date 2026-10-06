@extends('layouts.landing')

@section('title', $county->name.' Aspirants, Pages & Links - My Leader Kenya')
@section('meta_description', 'Aspirants, official pages and community links for '.$county->name.', Kenya.')
@section('og_image', $county->image ? Storage::url($county->image) : asset('images/myleader.png'))

@section('content')
@include('components.aspirant-card-styles')
<script type="application/ld+json">
@php
    $countySchemaItems = [];
    $countySchemaPosition = 1;
    foreach (($candidateGroups ?? collect()) as $candidateGroup) {
        foreach ($candidateGroup['candidates'] as $schemaCandidate) {
            $countySchemaItems[] = [
                '@type' => 'ListItem',
                'position' => $countySchemaPosition++,
                'name' => $schemaCandidate->name,
                'url' => route('aspirants.show', $schemaCandidate),
            ];
        }
    }
    $countySchema = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => 'Aspirants in '.$county->name,
        'description' => 'Aspirants, official pages and community links for '.$county->name.', Kenya.',
        'url' => route('county.show', $county),
        'isPartOf' => ['@type' => 'WebSite', 'name' => 'My Leader Kenya'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $countySchemaItems,
        ],
    ];
@endphp
{{ json_encode($countySchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}
</script>
<div class="county-page">
    <div class="county-page-shell">
        @include('components.frontend-nav')

        <section class="county-hero">
            <div class="county-hero-text">
                <p class="county-hero-kicker">County</p>
                <h1 class="county-hero-name">{{ $county->name }}</h1>
                @if($county->capital)
                    <p class="county-hero-sub">Capital: {{ $county->capital }}</p>
                @endif
                <div class="county-hero-stats">
                    @if($county->population)
                        <div class="county-hero-stat"><span>Population</span><strong>{{ number_format($county->population) }}</strong></div>
                    @endif
                    @if($county->registered_voters)
                        <div class="county-hero-stat"><span>Registered voters</span><strong>{{ number_format($county->registered_voters) }}</strong></div>
                    @endif
                    <div class="county-hero-stat"><span>Aspirants</span><strong>{{ number_format($candidateTotal) }}</strong></div>
                    <div class="county-hero-stat"><span>Pages &amp; links</span><strong>{{ $links->count() }}</strong></div>
                </div>
                <div class="county-hero-actions">
                    <a class="county-hero-action primary" href="{{ route('aspirants.public', ['county' => $county->name]) }}"><i class="fas fa-users"></i> Browse all aspirants</a>
                    <a class="county-hero-action" href="{{ route('counties.public') }}"><i class="fas fa-arrow-left"></i> All counties</a>
                </div>
            </div>
            @if($county->image)
                <div class="county-hero-image">
                    <img src="{{ Storage::url($county->image) }}" alt="{{ $county->name }} map" loading="lazy">
                </div>
            @endif
        </section>

        <x-video-rail-layout>
            @include('components.position-aspirant-groups', [
                'groups' => $candidateGroups,
                'headingId' => 'county-aspirants-title',
                'title' => 'Aspirants in '.$county->name,
                'subtitle' => 'Approved aspirants running in '.$county->name.', from Governor to MCA.',
                'total' => $candidateTotal,
                'gridClass' => 'county-position-grid',
                'emptyTitle' => 'No approved aspirants yet',
                'emptyText' => 'Approved aspirants for '.$county->name.' will appear here.',
            ])
        </x-video-rail-layout>

        @include('components.pages-links-list', [
            'resourceLinks' => $links,
            'pagesLinksTitle' => $county->name.' Pages & Links',
        ])

        @if($constituencies->isNotEmpty())
            <section class="county-constituencies" aria-labelledby="countyConstituenciesTitle">
                <div class="county-section-head">
                    <h2 id="countyConstituenciesTitle">Constituencies</h2>
                </div>
                <div class="county-constituency-grid">
                    @foreach($constituencies as $constituency)
                        <a class="county-constituency-card" href="{{ route('aspirants.public', ['county' => $county->name, 'constituency' => $constituency->name]) }}">
                            <span class="county-constituency-name">{{ $constituency->name }}</span>
                            <span class="county-constituency-meta">{{ $constituency->approved_aspirant_count }} aspirant{{ $constituency->approved_aspirant_count !== 1 ? 's' : '' }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>

<style>
    .county-page { min-height: 100vh; background: #0a0a0a; color: #f5f5f0; }
    .county-page-shell { max-width: 1240px; margin: 0 auto; padding: 0 18px 60px; }
    .county-hero { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 26px; align-items: center; border: 1px solid #26262a; border-radius: 20px; background: #111113; padding: clamp(22px, 4vw, 40px); margin-top: 22px; }
    .county-hero-kicker { margin: 0; color: #34d399; font-size: 11px; font-weight: 900; letter-spacing: .2em; text-transform: uppercase; }
    .county-hero-name { margin: 8px 0 0; font-size: clamp(30px, 5vw, 46px); font-weight: 800; }
    .county-hero-sub { margin: 6px 0 0; color: #a1a1aa; font-size: 14px; }
    .county-hero-stats { display: flex; flex-wrap: wrap; gap: 22px; margin-top: 22px; }
    .county-hero-stat span, .county-hero-stat strong { display: block; }
    .county-hero-stat span { color: #71717a; font-size: 10px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
    .county-hero-stat strong { margin-top: 4px; font-size: 20px; }
    .county-hero-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
    .county-hero-action { display: inline-flex; align-items: center; gap: 8px; border: 1px solid #3f3f46; border-radius: 11px; padding: 11px 18px; color: #d4d4d8; font-size: 13px; font-weight: 800; text-decoration: none; }
    .county-hero-action.primary { border-color: transparent; background: #059669; color: #fff; }
    .county-hero-image img { width: 100%; border-radius: 16px; object-fit: cover; }
    .county-constituencies { margin-top: 30px; }
    .county-position-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .county-section-head { display: flex; align-items: baseline; justify-content: space-between; gap: 14px; margin-bottom: 14px; }
    .county-section-head h2 { margin: 0; font-size: 22px; font-weight: 800; }
    .county-section-link { color: #34d399; font-size: 13px; font-weight: 800; text-decoration: none; }
    .county-constituency-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px; }
    .county-constituency-card { display: flex; flex-direction: column; gap: 5px; border: 1px solid #27272a; border-radius: 14px; background: #111113; padding: 14px 16px; color: #fff; text-decoration: none; }
    .county-constituency-card:hover { border-color: #059669; }
    .county-constituency-name { font-size: 14px; font-weight: 800; }
    .county-constituency-meta { color: #71717a; font-size: 12px; }
    @media (max-width: 820px) { .county-hero { grid-template-columns: 1fr; } .county-position-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px) { .county-position-grid { grid-template-columns: 1fr; } }
</style>
@endsection