@extends('layouts.app')

@section('page_title', 'Create News Article')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white">Create News Article</h1>
        <a href="{{ route('news.index') }}" class="text-zinc-400 hover:text-white">← Back to Articles</a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Article Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Tags -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Tags</label>
                    <select name="tags[]" multiple 
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white h-40">
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-zinc-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple</p>
                </div>

                <!-- Tagged Candidates -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Tagged Aspirants / Candidates</label>
                    <select name="candidates[]" multiple 
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white h-40">
                        @foreach($candidates as $candidate)
                            <option value="{{ $candidate->id }}">
                                {{ $candidate->name }} {{ $candidate->nick_name ? '(' . $candidate->nick_name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Status</label>
                    <select name="status" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </div>

            <!-- Excerpt -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Excerpt / Summary</label>
                <textarea name="excerpt" rows="3" 
                          class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></textarea>
            </div>

            <!-- Content -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Full Content <span class="text-red-500">*</span></label>
                <textarea name="content" rows="14" 
                          class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></textarea>
            </div>

            <!-- Media -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Featured Image</label>
                    <input type="file" name="featured_image" accept="image/*"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Video URL (Optional)</label>
                    <input type="url" name="video_url" placeholder="https://youtube.com/watch?v=..." 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
            </div>

            <div class="mt-10">
                <button type="submit" 
                        class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold text-lg">
                    Create Article
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
