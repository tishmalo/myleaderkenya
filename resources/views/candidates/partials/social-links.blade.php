@php
    $candidate = $candidate ?? null;
    $socialLinks = [
        'facebook_url' => ['label' => 'Facebook', 'icon' => 'fa-brands fa-facebook-f', 'placeholder' => 'https://facebook.com/profile'],
        'x_url' => ['label' => 'X', 'icon' => 'fa-brands fa-x-twitter', 'placeholder' => 'https://x.com/handle'],
        'instagram_url' => ['label' => 'Instagram', 'icon' => 'fa-brands fa-instagram', 'placeholder' => 'https://instagram.com/handle'],
        'tiktok_url' => ['label' => 'TikTok', 'icon' => 'fa-brands fa-tiktok', 'placeholder' => 'https://tiktok.com/@handle'],
        'youtube_url' => ['label' => 'YouTube', 'icon' => 'fa-brands fa-youtube', 'placeholder' => 'https://youtube.com/@channel'],
    ];
@endphp

<div class="mt-6 rounded-3xl border border-zinc-800 bg-zinc-950 p-6">
    <div class="mb-5">
        <h2 class="text-xl font-semibold text-white flex items-center gap-2">
            <i class="fas fa-share-nodes text-emerald-500"></i>
            Social Media
        </h2>
        <p class="mt-1 text-sm text-zinc-500">Add public campaign profiles for this aspirant.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($socialLinks as $field => $meta)
            <div>
                <label class="mb-2 flex items-center gap-2 text-sm text-zinc-400">
                    <i class="{{ $meta['icon'] }} text-emerald-500"></i>
                    {{ $meta['label'] }}
                </label>
                <input
                    type="url"
                    name="{{ $field }}"
                    value="{{ old($field, $candidate?->{$field}) }}"
                    placeholder="{{ $meta['placeholder'] }}"
                    class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white placeholder:text-zinc-500"
                >
                @error($field)
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
        @endforeach
    </div>
</div>
