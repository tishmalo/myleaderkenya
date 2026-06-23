@extends('layouts.app')

@section('page_title', 'News Categories')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-tags text-emerald-500"></i> 
            News Categories
        </h1>
        <a href="{{ route('categories.create') }}" class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl flex items-center gap-2">
            <i class="fas fa-plus"></i> New Category
        </a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr>
                    <th class="px-6 py-4 text-left">Category</th>
                    <th class="px-6 py-4 text-left">Description</th>
                    <th class="px-6 py-4 text-center">Articles</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($categories as $category)
                <tr class="hover:bg-zinc-800/70">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded" style="background-color: {{ $category->color ?? '#3b82f6' }}"></div>
                            <span class="font-medium">{{ $category->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-zinc-400">{{ Str::limit($category->description, 80) }}</td>
                    <td class="px-6 py-4 text-center">{{ $category->articles_count }}</td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('categories.edit', $category) }}" class="text-blue-400 hover:text-blue-500 mx-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteCategory({{ $category->id }}, '{{ addslashes($category->name) }}')" 
                                class="text-red-400 hover:text-red-500 mx-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-16 text-zinc-500">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteCategory(id, name) {
    showDeleteModal(`/categories/${id}`, `Delete category <strong>${name}</strong>?`);
}
</script>
@endpush