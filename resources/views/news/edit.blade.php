@extends('layouts.app')

@section('page_title', 'Edit News Article')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white">Edit News Article</h1>
        <a href="{{ route('news.index') }}" class="text-zinc-400 hover:text-white">← Back to Articles</a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('news.update', $news) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Article Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $news->title) }}" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div class="md:col-span-2">
                    <x-searchable-multiselect
                        name="tags[]"
                        label="Tags"
                        :options="$tags->map(fn ($tag) => ['value' => $tag->id, 'label' => $tag->name])"
                        :selected="old('tags', $news->tags->pluck('id')->all())"
                        placeholder="Search tags..."
                        empty-text="No matching tags found." />
                </div>

                <div class="md:col-span-2">
                    <x-searchable-multiselect
                        name="candidates[]"
                        label="Tagged Aspirants"
                        :options="$candidates->map(fn ($candidate) => ['value' => $candidate->id, 'label' => trim($candidate->name . ($candidate->nick_name ? ' (' . $candidate->nick_name . ')' : ''))])"
                        :selected="old('candidates', $news->candidates->pluck('id')->all())"
                        placeholder="Search aspirants..."
                        empty-text="No matching aspirants found." />
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Status</label>
                    <select name="status" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                        <option value="draft" {{ $news->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ $news->status == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
            </div>

            <!-- Excerpt -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Excerpt</label>
                <textarea name="excerpt" rows="3" 
                          class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">{{ old('excerpt', $news->excerpt) }}</textarea>
            </div>

            <!-- Content -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Full Content <span class="text-red-500">*</span></label>
                <textarea name="content" rows="14" 
                          class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">{{ old('content', $news->content) }}</textarea>
            </div>

            <!-- Media -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Featured Image</label>
                    @if($news->featured_image)
                        <div class="mb-3">
                            <img src="{{ Storage::url($news->featured_image) }}" 
                                 class="max-h-48 rounded-2xl border border-zinc-700">
                        </div>
                    @endif
                    <input type="file" name="featured_image" accept="image/*"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Video URL</label>
                    <input type="url" name="video_url" value="{{ old('video_url', $news->video_url) }}"
                           placeholder="https://youtube.com/..." 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
            </div>

            <div class="mt-10 flex gap-4">
                <a href="{{ route('news.index') }}" 
                   class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">
                    Update Article
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
