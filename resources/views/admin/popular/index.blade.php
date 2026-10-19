@extends('layouts.app')

@section('page_title', 'Popular Content')

@section('content')
@php
    $totalViews = $articles->sum('views') + $aspirants->sum('views') + $polls->sum('views') + $parties->sum('views');
@endphp

<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl px-6 py-5">
        <h1 class="text-2xl font-semibold text-white flex items-center gap-3">
            <i class="fas fa-fire text-emerald-400"></i>
            Popular Content
        </h1>
        <p class="text-sm text-zinc-500 mt-2">
            The most viewed stories, aspirants, polls and parties. Counts come from the public pages: one view per
            visitor per item per day, with staff previews and crawlers left out — so these are real readers.
        </p>
        <p class="text-xs text-zinc-600 mt-3">
            {{ number_format($totalViews) }} views across the listed items
        </p>
    </div>

    @php
        $cards = [
            [
                'label' => 'News stories',
                'icon' => 'fas fa-newspaper',
                'rows' => $articles,
                'label_key' => 'title',
                'public' => fn ($row) => $row->slug ? route('news.public.show', $row->slug) : null,
                'edit' => fn ($row) => route('news.edit', $row->id),
                'empty' => 'No published stories yet.',
            ],
            [
                'label' => 'Aspirants',
                'icon' => 'fas fa-user-tie',
                'rows' => $aspirants,
                'label_key' => 'name',
                'public' => fn ($row) => route('aspirants.show', $row->id),
                'edit' => fn ($row) => route('candidates.edit', $row->id),
                'empty' => 'No approved aspirants yet.',
            ],
            [
                'label' => 'Polls',
                'icon' => 'fas fa-chart-simple',
                'rows' => $polls,
                'label_key' => 'question',
                'public' => fn ($row) => $row->slug ? route('poll.show', $row->slug) : null,
                'edit' => fn ($row) => route('polls.edit', $row->id),
                'empty' => 'No polls yet.',
            ],
            [
                'label' => 'Political parties',
                'icon' => 'fas fa-landmark',
                'rows' => $parties,
                'label_key' => 'name',
                'public' => fn ($row) => $row->slug ? route('parties.show', $row->slug) : null,
                'edit' => fn ($row) => route('political-parties.edit', $row->id),
                'empty' => 'No parties yet.',
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        @foreach($cards as $card)
            <section class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
                <div class="bg-zinc-950 px-5 py-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-zinc-100 flex items-center gap-2">
                        <i class="{{ $card['icon'] }} text-emerald-400"></i> {{ $card['label'] }}
                    </h2>
                    <span class="text-xs text-zinc-500">{{ number_format($card['rows']->sum('views')) }} views</span>
                </div>

                <div class="divide-y divide-zinc-800">
                    @forelse($card['rows'] as $index => $row)
                        @php
                            $label = (string) ($row->{$card['label_key']} ?? '');
                            $publicUrl = $card['public']($row);
                        @endphp
                        <div class="flex items-center gap-3 px-5 py-3 text-sm">
                            <span class="w-5 shrink-0 text-xs font-semibold text-zinc-600">{{ $index + 1 }}</span>
                            <span class="min-w-0 flex-1 truncate text-zinc-200">
                                @if($publicUrl)
                                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="hover:text-white hover:underline">{{ $label ?: 'Untitled' }}</a>
                                @else
                                    {{ $label ?: 'Untitled' }}
                                @endif
                            </span>
                            <span class="shrink-0 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-400 whitespace-nowrap">
                                {{ number_format((int) $row->views) }}
                            </span>
                            <a href="{{ $card['edit']($row) }}" class="shrink-0 text-zinc-500 hover:text-white" title="Manage">
                                <i class="fas fa-pen"></i>
                            </a>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-zinc-500">{{ $card['empty'] }}</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection
