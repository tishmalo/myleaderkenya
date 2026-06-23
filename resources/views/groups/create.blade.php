@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <h1 class="text-3xl font-semibold text-white mb-8">Create New Group</h1>

        <form method="POST" action="{{ route('groups.store') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-2">Group Name</label>
                    <input type="text" 
                           name="name" 
                           required
                           maxlength="100"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-2">Description (Optional)</label>
                    <textarea name="description" 
                              rows="4"
                              maxlength="500"
                              class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit"
                            class="w-full bg-violet-600 hover:bg-violet-700 py-4 rounded-2xl font-semibold text-lg transition-colors">
                        Create Group
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection