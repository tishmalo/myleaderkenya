@php($tool = $campaignTool ?? null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label class="block text-sm text-zinc-400 mb-2">Title <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $tool?->title) }}" required
               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
        @error('title')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-zinc-400 mb-2">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $tool?->slug) }}" placeholder="bulk-sms"
               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
        <p class="text-xs text-zinc-500 mt-1">Leave blank to generate from title.</p>
        @error('slug')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-zinc-400 mb-2">Navigation Label</label>
        <input type="text" name="nav_label" value="{{ old('nav_label', $tool?->nav_label) }}" placeholder="Bulk SMS"
               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
        @error('nav_label')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-zinc-400 mb-2">Status</label>
        <select name="status" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            <option value="draft" {{ old('status', $tool?->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ old('status', $tool?->status) === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        @error('status')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-zinc-400 mb-2">Sort Order</label>
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $tool?->sort_order ?? 0) }}"
               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
        @error('sort_order')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6">
    <label class="block text-sm text-zinc-400 mb-2">Excerpt / Summary</label>
    <textarea name="excerpt" rows="3" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">{{ old('excerpt', $tool?->excerpt) }}</textarea>
    @error('excerpt')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
</div>

<div class="mt-6">
    <label class="block text-sm text-zinc-400 mb-2">Content <span class="text-red-500">*</span></label>
    <textarea name="content" rows="14" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">{{ old('content', $tool?->content) }}</textarea>
    @error('content')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <div>
        <label class="block text-sm text-zinc-400 mb-2">Featured Image</label>
        @if($tool?->featured_image)
            <div class="mb-3">
                <img src="{{ Storage::url($tool->featured_image) }}" class="max-h-48 rounded-2xl border border-zinc-700" alt="{{ $tool->title }}">
            </div>
        @endif
        <input type="file" name="featured_image" accept="image/*"
               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
        @error('featured_image')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-4">
        <div>
            <label class="block text-sm text-zinc-400 mb-2">SEO Title</label>
            <input type="text" name="meta_title" value="{{ old('meta_title', $tool?->meta_title) }}"
                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
            @error('meta_title')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm text-zinc-400 mb-2">SEO Description</label>
            <textarea name="meta_description" rows="3" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">{{ old('meta_description', $tool?->meta_description) }}</textarea>
            @error('meta_description')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
        </div>
    </div>
</div>