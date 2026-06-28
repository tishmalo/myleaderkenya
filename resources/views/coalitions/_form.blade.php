@php($item = $coalition ?? null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label class="block text-sm text-zinc-400 mb-2">Coalition Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $item?->name) }}" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
        @error('name')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-zinc-400 mb-2">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $item?->slug) }}" placeholder="coalition-name" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
        <p class="text-xs text-zinc-500 mt-1">Leave blank to generate from name.</p>
        @error('slug')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-zinc-400 mb-2">Status</label>
        <select name="status" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            <option value="draft" {{ old('status', $item?->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ old('status', $item?->status) === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        @error('status')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-zinc-400 mb-2">Sort Order</label>
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $item?->sort_order ?? 0) }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
        @error('sort_order')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-zinc-400 mb-2">Brand Color</label>
        <input type="text" name="brand_color" value="{{ old('brand_color', $item?->brand_color) }}" placeholder="#BB0000" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
        @error('brand_color')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6">
    <label class="block text-sm text-zinc-400 mb-2">Member Political Parties</label>
    <select name="political_parties[]" multiple class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white h-48">
        @foreach($politicalParties as $party)
            <option value="{{ $party->id }}" {{ collect(old('political_parties', $item?->politicalParties?->pluck('id')->all() ?? []))->contains($party->id) ? 'selected' : '' }}>
                {{ $party->name }}{{ $party->abbreviation ? ' (' . $party->abbreviation . ')' : '' }}
            </option>
        @endforeach
    </select>
    <p class="text-xs text-zinc-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</p>
    @error('political_parties')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
</div>

<div class="mt-6">
    <label class="block text-sm text-zinc-400 mb-2">Excerpt / Summary</label>
    <textarea name="excerpt" rows="3" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">{{ old('excerpt', $item?->excerpt) }}</textarea>
    @error('excerpt')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
</div>

<div class="mt-6">
    <label class="block text-sm text-zinc-400 mb-2">Content <span class="text-red-500">*</span></label>
    <textarea name="content" rows="14" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">{{ old('content', $item?->content) }}</textarea>
    @error('content')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <div>
        <label class="block text-sm text-zinc-400 mb-2">Coalition Logo</label>
        @if($item?->logo)
            <div class="mb-3"><img src="{{ Storage::url($item->logo) }}" class="max-h-48 rounded-2xl border border-zinc-700" alt="{{ $item->name }}"></div>
        @endif
        <input type="file" name="logo" accept="image/*" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
        @error('logo')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-4">
        <div>
            <label class="block text-sm text-zinc-400 mb-2">SEO Title</label>
            <input type="text" name="meta_title" value="{{ old('meta_title', $item?->meta_title) }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
            @error('meta_title')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm text-zinc-400 mb-2">SEO Description</label>
            <textarea name="meta_description" rows="3" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">{{ old('meta_description', $item?->meta_description) }}</textarea>
            @error('meta_description')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
