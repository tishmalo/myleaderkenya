@extends('layouts.app')

@section('page_title', 'SEO Pages')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-semibold text-white">SEO Pages</h1>
        <p class="text-zinc-400 mt-2">Manage the titles, descriptions and canonical URLs search engines use for the public pages.</p>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-zinc-950/60 text-xs uppercase tracking-wider text-zinc-500">
                <tr>
                    <th class="px-6 py-4">Page</th>
                    <th class="px-6 py-4">Meta Title</th>
                    <th class="px-6 py-4">Meta Description</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
            @forelse($pages as $page)
                <tr>
                    <td class="px-6 py-5 align-top">
                        <div class="font-medium text-white">{{ $page->route_name }}</div>
                        <div class="text-xs text-zinc-500 mt-1">{{ $page->canonical ?: 'Uses the page URL' }}</div>
                    </td>
                    <td class="px-6 py-5 align-top text-zinc-300">{{ $page->title }}</td>
                    <td class="px-6 py-5 align-top text-zinc-400 text-sm max-w-md">{{ \Illuminate\Support\Str::limit($page->description, 110) }}</td>
                    <td class="px-6 py-5 align-top text-right">
                        <a href="{{ route('seo-pages.edit', $page) }}" class="inline-flex items-center gap-2 border border-zinc-700 hover:bg-zinc-800 px-4 py-2 rounded-xl text-sm font-medium text-zinc-200 transition-colors">
                            <i class="fas fa-edit"></i>
                            <span>Edit</span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-zinc-500">
                        No SEO pages yet. Seed the defaults with <code class="text-zinc-300">php artisan db:seed --class=SeoPageSeeder</code>.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
