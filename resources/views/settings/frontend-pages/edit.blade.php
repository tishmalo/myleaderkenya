@extends('layouts.app')

@section('page_title', 'Edit ' . $pageData['label'])

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold text-white">Edit {{ $pageData['label'] }}</h1>
            <p class="text-zinc-400 mt-2">Update public page content and SEO metadata.</p>
        </div>
        <a href="{{ route('frontend-pages.index') }}" class="text-zinc-400 hover:text-white">Back to Pages</a>
    </div>

    <form action="{{ route('frontend-pages.update', $pageData['key']) }}" method="POST" class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Page Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $pageData['content']['title']) }}" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                @error('title') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Hero Title <span class="text-red-500">*</span></label>
                <input type="text" name="hero_title" value="{{ old('hero_title', $pageData['content']['hero_title']) }}" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                @error('hero_title') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-2">Excerpt</label>
            <textarea name="excerpt" rows="3" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">{{ old('excerpt', $pageData['content']['excerpt']) }}</textarea>
            @error('excerpt') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-2">Content <span class="text-red-500">*</span></label>
            <textarea name="content" rows="12" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">{{ old('content', $pageData['content']['content']) }}</textarea>
            @error('content') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="block text-sm text-zinc-400 mb-2">CTA Label</label>
                <input type="text" name="cta_label" value="{{ old('cta_label', $pageData['content']['cta_label']) }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                @error('cta_label') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">CTA URL</label>
                <input type="text" name="cta_url" value="{{ old('cta_url', $pageData['content']['cta_url']) }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                @error('cta_url') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $pageData['content']['meta_title']) }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                @error('meta_title') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Meta Description</label>
                <textarea name="meta_description" rows="3" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">{{ old('meta_description', $pageData['content']['meta_description']) }}</textarea>
                @error('meta_description') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex gap-4 pt-4">
            <a href="{{ route('frontend-pages.index') }}" class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800 transition-colors">Cancel</a>
            <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold transition-colors">Save Page</button>
        </div>
    </form>
</div>
@endsection
