@extends('layouts.app')

@section('page_title', 'Edit SEO Page')

@section('content')
@php($pageUrl = \Illuminate\Support\Facades\Route::has($seoPage->route_name) ? route($seoPage->route_name) : null)

<div class="space-y-8">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold text-white">Edit SEO Page</h1>
            <p class="text-zinc-400 mt-2">Metadata search engines and social networks use for <code class="text-zinc-200">{{ $seoPage->route_name }}</code>.</p>
        </div>
        <a href="{{ route('seo-pages.index') }}" class="text-zinc-400 hover:text-white">Back to SEO Pages</a>
    </div>

    <form action="{{ route('seo-pages.update', $seoPage) }}" method="POST" class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm text-zinc-400 mb-2">Meta Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $seoPage->title) }}" required maxlength="255" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
            @error('title') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
            <p class="text-xs text-zinc-500 mt-2">Keep it under 60 characters so search results do not cut it off.</p>
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-2">Meta Description <span class="text-red-500">*</span></label>
            <textarea name="description" rows="4" required maxlength="160" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">{{ old('description', $seoPage->description) }}</textarea>
            @error('description') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
            <p class="text-xs text-zinc-500 mt-2">Up to 160 characters.</p>
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-2">Canonical URL</label>
            <input type="url" name="canonical" value="{{ old('canonical', $seoPage->canonical) }}" placeholder="{{ $pageUrl ?: 'Leave blank to use the page URL' }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
            @error('canonical') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
            <p class="text-xs text-zinc-500 mt-2">
                Leave blank to use {{ $pageUrl ?: 'the public page URL' }}.
            </p>
        </div>

        <div class="flex gap-4 pt-4">
            <a href="{{ route('seo-pages.index') }}" class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800 transition-colors">Cancel</a>
            <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold transition-colors">Save SEO Page</button>
        </div>
    </form>
</div>
@endsection
