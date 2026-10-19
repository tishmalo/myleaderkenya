@extends('layouts.app')

@section('page_title', 'Coordinators Guide')

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="flex items-center gap-3 text-3xl font-semibold text-white">
                <i class="fas fa-book-open text-emerald-500"></i> Coordinators Guide
            </h1>
            <p class="mt-2 max-w-2xl text-sm text-zinc-400">
                How we work: which part of the admin area handles which job, and what a good day as a coordinator looks like.
            </p>
            @unless($isCustomised)
                <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-zinc-800 px-3 py-1 text-xs text-zinc-400">
                    <i class="fas fa-circle-info text-zinc-500"></i>
                    You are reading the built-in guide. Save your own version to make it the team's.
                </p>
            @endunless
        </div>

        @if($canEdit)
            <a href="#edit-guide" class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-medium text-white transition-colors hover:bg-emerald-700">
                <i class="fas fa-pen-to-square"></i> Edit this guide
            </a>
        @endif
    </div>

    <div class="grid gap-8 lg:grid-cols-[240px_minmax(0,1fr)]">
        @if(! empty($headings))
            <aside class="lg:sticky lg:top-4 lg:self-start">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">On this page</p>
                <nav class="space-y-1 border-l border-zinc-800 pl-3">
                    @foreach($headings as $heading)
                        @continue($heading['level'] > 3)
                        <a href="#{{ $heading['anchor'] }}"
                           class="block rounded-lg py-1 text-sm text-zinc-400 transition-colors hover:text-emerald-400 {{ $heading['level'] === 3 ? 'pl-4 text-xs' : 'font-medium' }}">
                            {{ $heading['title'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>
        @endif

        <article class="guide-prose rounded-3xl border border-zinc-800 bg-zinc-900 p-6 md:p-10">
            {!! $html !!}
        </article>
    </div>

    @if($canEdit)
        <section id="edit-guide" class="mt-8">
            <details class="rounded-3xl border border-zinc-800 bg-zinc-900" {{ request()->has('edit') || $errors->any() ? 'open' : '' }}>
                <summary class="cursor-pointer select-none rounded-3xl px-6 py-5 text-sm font-semibold text-zinc-200 md:px-8">
                    <i class="fas fa-pen-to-square mr-2 text-emerald-500"></i> Edit this guide
                </summary>

                <form action="{{ route('coordinators-guide.update') }}" method="POST" class="border-t border-zinc-800 p-6 md:p-8">
                    @csrf
                    @method('PUT')

                    <label for="body" class="mb-2 block text-sm text-zinc-400">The guide, in markdown</label>
                    <textarea id="body" name="body" rows="26" required
                              class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 font-mono text-sm leading-relaxed text-white focus:border-emerald-500 focus:outline-none">{{ old('body', $markdown) }}</textarea>

                    <div class="mt-2 flex flex-col gap-1 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
                        <span><code class="text-zinc-400">##</code> starts a section, <code class="text-zinc-400">-</code> a bullet, <code class="text-zinc-400">**bold**</code> for emphasis.</span>
                        <span>Leave it better than you found it.</span>
                    </div>

                    @error('body')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror

                    <div class="mt-6 flex items-center gap-3">
                        <button type="submit" class="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-emerald-700">
                            Save guide
                        </button>
                        <a href="{{ route('coordinators-guide.index') }}" class="rounded-2xl border border-zinc-700 px-6 py-3 text-sm font-medium text-zinc-300 transition-colors hover:text-white">Cancel</a>
                    </div>
                </form>
            </details>
        </section>
    @endif
</div>
@endsection

@push('styles')
<style>
    .guide-prose { color: #d4d4d8; font-size: 0.95rem; line-height: 1.75; }
    .guide-prose > *:first-child { margin-top: 0; }
    .guide-prose h2 {
        margin: 2.5rem 0 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #27272a;
        color: #fff;
        font-size: 1.4rem;
        font-weight: 600;
        scroll-margin-top: 1.5rem;
    }
    .guide-prose h2:first-child { margin-top: 0; }
    .guide-prose h3 {
        margin: 1.75rem 0 0.5rem;
        color: #fff;
        font-size: 1.05rem;
        font-weight: 600;
        scroll-margin-top: 1.5rem;
    }
    .guide-prose h4 { margin: 1.25rem 0 0.5rem; color: #e4e4e7; font-size: 0.95rem; font-weight: 600; }
    .guide-prose p { margin: 0.75rem 0; }
    .guide-prose ul, .guide-prose ol { margin: 0.75rem 0 0.75rem 1.25rem; }
    .guide-prose ul { list-style: disc; }
    .guide-prose ol { list-style: decimal; }
    .guide-prose li { margin: 0.35rem 0; padding-left: 0.25rem; }
    .guide-prose li::marker { color: #10b981; }
    .guide-prose strong { color: #fff; font-weight: 600; }
    .guide-prose a { color: #34d399; text-decoration: underline; text-underline-offset: 2px; }
    .guide-prose code {
        background: #18181b;
        border: 1px solid #27272a;
        border-radius: 0.375rem;
        padding: 0.1rem 0.35rem;
        color: #a7f3d0;
        font-size: 0.85em;
    }
    .guide-prose pre { margin: 1rem 0; overflow-x: auto; background: #18181b; border: 1px solid #27272a; border-radius: 1rem; padding: 1rem; }
    .guide-prose pre code { border: 0; background: transparent; padding: 0; color: #d4d4d8; }
    .guide-prose blockquote {
        margin: 1rem 0;
        border-left: 3px solid #10b981;
        background: #18181b;
        border-radius: 0 0.75rem 0.75rem 0;
        padding: 0.75rem 1rem;
        color: #a1a1aa;
    }
    .guide-prose hr { margin: 2rem 0; border-color: #27272a; }
</style>
@endpush
