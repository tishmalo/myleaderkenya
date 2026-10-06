@extends('layouts.app')

@section('page_title', 'Sitemap')

@section('content')
<div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3">
            <i class="fas fa-sitemap text-emerald-500"></i> Sitemap
            <span class="text-sm font-normal text-zinc-500">What search engines can discover, refreshed daily</span>
        </h1>
        <form action="{{ route('sitemap.regenerate') }}" method="POST">
            @csrf
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
                <i class="fas fa-rotate"></i> Regenerate now
            </button>
        </form>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden mb-8">
        <div class="px-6 py-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-medium text-white">Public sitemap index</p>
                <a href="{{ $indexUrl }}" target="_blank" rel="noopener" class="text-xs text-blue-400 hover:underline break-all">{{ $indexUrl }}</a>
            </div>
            <p class="text-sm text-zinc-400">{{ number_format($totalUrls) }} URLs across {{ $files->count() }} {{ Str::plural('file', $files->count()) }}</p>
        </div>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr>
                    <th class="px-6 py-4 text-left">File</th>
                    <th class="px-6 py-4 text-right">URLs</th>
                    <th class="px-6 py-4 text-right">Last changed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 text-zinc-300">
                @forelse($files as $file)
                    <tr class="hover:bg-zinc-800/70">
                        <td class="px-6 py-4">
                            <a href="{{ route('sitemap.file', ['file' => $file['name']]) }}" target="_blank" rel="noopener" class="text-blue-400 hover:underline break-all">/sitemaps/{{ $file['name'] }}.xml</a>
                        </td>
                        <td class="px-6 py-4 text-right">{{ number_format($file['urls']) }}</td>
                        <td class="px-6 py-4 text-right">{{ $file['lastmod'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center py-16 text-zinc-500">No public pages to list yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
