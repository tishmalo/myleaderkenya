@extends('layouts.app')

@section('page_title', 'News Articles')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-newspaper text-emerald-500"></i> 
            News Articles
        </h1>
        <a href="{{ route('news.create') }}" 
           class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i> New Article
        </a>
    </div>

    <!-- Tag Filter -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('news.index') }}" 
           class="px-5 py-2 rounded-2xl text-sm font-medium {{ !request('tag') ? 'bg-emerald-600 text-white' : 'bg-zinc-800 hover:bg-zinc-700 text-zinc-300' }}">
            All Articles
        </a>
        
        @foreach($tags as $tag)
            <a href="{{ route('news.index', ['tag' => $tag->slug]) }}" 
               class="px-5 py-2 rounded-2xl text-sm font-medium flex items-center gap-2
                      {{ request('tag') == $tag->slug ? 'bg-emerald-600 text-white' : 'bg-zinc-800 hover:bg-zinc-700 text-zinc-300' }}">
                <span class="w-3 h-3 rounded-full" style="background-color: {{ $tag->color ?? '#3b82f6' }}"></span>
                {{ $tag->name }}
            </a>
        @endforeach
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr>
                    <th class="px-6 py-4 text-left">Article</th>
                    <th class="px-6 py-4 text-left">Tags</th>
                    <th class="px-6 py-4 text-left">Tagged Aspirants</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($articles as $article)
                <tr class="hover:bg-zinc-800/70">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            @if($article->featured_image)
                                <img src="{{ Storage::url($article->featured_image) }}" 
                                     class="w-12 h-12 object-cover rounded-xl">
                            @endif
                            <div>
                                <p class="font-medium text-white">{{ $article->title }}</p>
                                <p class="text-xs text-zinc-500">
                                    {{ $article->author->name ?? 'Unknown' }} • 
                                    {{ $article->created_at->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @foreach($article->tags as $cat)
                            <span class="inline-block px-3 py-1 text-xs rounded-full mr-1 mb-1"
                                  style="background-color: {{ $cat->color ?? '#3b82f6' }}20; color: {{ $cat->color ?? '#3b82f6' }}">
                                {{ $cat->name }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-400">
                        {{ $article->candidates->pluck('name')->join(', ') ?: '—' }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($article->status === 'published')
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-emerald-500/20 text-emerald-400">Published</span>
                        @else
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-orange-500/20 text-orange-400">Draft</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('news.edit', $article) }}" class="text-blue-400 hover:text-blue-500 mx-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteArticle({{ $article->id }}, '{{ addslashes($article->title) }}')" 
                                class="text-red-400 hover:text-red-500 mx-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-16 text-zinc-500">No articles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-center">
        {{ $articles->appends(request()->query())->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteArticle(id, title) {
    showDeleteModal(`/news/${id}`, `Delete article <strong>${title}</strong>?`);
}
</script>
@endpush


