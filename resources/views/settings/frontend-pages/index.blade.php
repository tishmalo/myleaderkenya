@extends('layouts.app')

@section('page_title', 'Frontend Pages')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-semibold text-white">Frontend Pages</h1>
        <p class="text-zinc-400 mt-2">Manage SEO pages used by the public navigation.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        @foreach($pages as $page)
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-white">{{ $page['label'] }}</h2>
                        <p class="text-zinc-400 mt-2">{{ $page['content']['excerpt'] }}</p>
                    </div>
                    <a href="{{ route($page['route']) }}" target="_blank" class="text-zinc-400 hover:text-white" title="Open public page">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>

                <div class="mt-6 flex gap-3">
                    <a href="{{ route('frontend-pages.edit', $page['key']) }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl font-medium text-white transition-colors">
                        <i class="fas fa-edit"></i>
                        <span>Edit Content</span>
                    </a>
                    <a href="{{ route($page['route']) }}" target="_blank" class="inline-flex items-center gap-2 border border-zinc-700 hover:bg-zinc-800 px-5 py-3 rounded-2xl font-medium text-zinc-200 transition-colors">
                        <i class="fas fa-eye"></i>
                        <span>Preview</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
