@extends('layouts.app')

@section('page_title', 'Campaign Website Samples')

@section('content')
<div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3"><i class="fas fa-images text-emerald-500"></i> Website Samples</h1>
        <a href="{{ route('campaign-website-requests.index') }}" class="bg-zinc-800 hover:bg-zinc-700 px-5 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-list mr-2"></i> Requests</a>
    </div>

    <form method="POST" action="{{ route('campaign-website-samples.store') }}" enctype="multipart/form-data" class="mb-8 bg-zinc-900 border border-zinc-800 rounded-3xl p-6 grid md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block text-sm text-zinc-400 mb-2">Title</label>
            <input name="title" value="{{ old('title') }}" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            @error('title')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm text-zinc-400 mb-2">Live Website Link</label>
            <input name="website_url" value="{{ old('website_url') }}" placeholder="https://..." class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            @error('website_url')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm text-zinc-400 mb-2">PNG / Preview Image</label>
            <input type="file" name="preview_image" accept="image/png,image/jpeg,image/webp" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-white">
            <p class="mt-2 text-xs text-zinc-500">Upload PNG, JPG, JPEG, or WEBP up to 5MB.</p>
            @error('preview_image')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Status</label>
                <select name="status" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Order</label>
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            </div>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm text-zinc-400 mb-2">Description</label>
            <textarea name="description" rows="3" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">{{ old('description') }}</textarea>
            @error('description')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <button class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium"><i class="fas fa-plus mr-2"></i> Add Sample</button>
        </div>
    </form>

    <div class="grid md:grid-cols-3 gap-5">
        @forelse($samples as $sample)
            <article class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
                @if($sample->image_url)
                    <img src="{{ $sample->image_url }}" alt="{{ $sample->title }}" class="w-full aspect-[4/3] object-cover bg-zinc-800">
                @else
                    <div class="w-full aspect-[4/3] bg-zinc-800 flex items-center justify-center text-zinc-500"><i class="fas fa-image text-4xl"></i></div>
                @endif
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <h2 class="text-xl font-semibold text-white">{{ $sample->title }}</h2>
                        <span class="text-xs px-2 py-1 rounded-full {{ $sample->status === 'published' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-orange-500/15 text-orange-300' }}">{{ ucfirst($sample->status) }}</span>
                    </div>
                    <p class="text-sm text-zinc-400 mb-4">{{ $sample->description ?: 'No description.' }}</p>
                    <div class="flex items-center justify-between gap-3">
                        @if($sample->website_url)
                            <a href="{{ $sample->website_url }}" target="_blank" class="text-emerald-400 hover:text-emerald-300 text-sm"><i class="fas fa-up-right-from-square mr-1"></i> Open</a>
                        @else
                            <span class="text-zinc-600 text-sm">No link</span>
                        @endif
                        <form method="POST" action="{{ route('campaign-website-samples.destroy', $sample) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-400 hover:text-red-300 text-sm"><i class="fas fa-trash mr-1"></i> Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="md:col-span-3 bg-zinc-900 border border-zinc-800 rounded-3xl p-12 text-center text-zinc-500">No samples added yet.</div>
        @endforelse
    </div>
</div>
@endsection

