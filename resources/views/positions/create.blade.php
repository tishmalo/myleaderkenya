@extends('layouts.app')

@section('page_title', 'Add New Position')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white">Add New Position</h1>
        <a href="{{ route('positions.index') }}" class="text-zinc-400 hover:text-white">← Back</a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('positions.store') }}" method="POST">
            @csrf
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Position Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                       placeholder="e.g. Member of Parliament">
            </div>

            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Description</label>
                <textarea name="description" rows="4"
                          class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                          placeholder="Brief description of the position..."></textarea>
            </div>

            <div class="mt-10">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">
                    Create Position
                </button>
            </div>
        </form>
    </div>
</div>
@endsection