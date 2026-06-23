@extends('layouts.app')

@section('page_title', 'Create New Category')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white">Create New Category</h1>
        <a href="{{ route('categories.index') }}" class="text-zinc-400 hover:text-white">← Back</a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Category Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                       placeholder="e.g. Politics, Development, Elections">
            </div>

            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Description</label>
                <textarea name="description" rows="4"
                          class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"></textarea>
            </div>

            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Color (Optional)</label>
                <input type="color" name="color" value="#3b82f6"
                       class="w-full h-12 bg-transparent border border-zinc-700 rounded-2xl cursor-pointer">
            </div>

            <div class="mt-10 flex gap-4">
                <a href="{{ route('categories.index') }}" 
                   class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>
@endsection