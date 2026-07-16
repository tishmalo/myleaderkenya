@extends('layouts.app')

@section('page_title', 'Create Bloc')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-10">
        <h1 class="text-3xl font-semibold mb-8">Create New Bloc</h1>

        <form action="{{ route('blocs.store') }}" method="POST">
            @csrf

            <div class="space-y-8">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Bloc Name</label>
                    <input type="text" name="name" required 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="e.g. Kikuyu Bloc">
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Counties / notes (comma separated)</label>
                    <input type="text" name="tribes" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="Nairobi, Kiambu, Nakuru">
                    <p class="text-xs text-zinc-500 mt-2">Optional: enter counties, communities, or regional notes separated by commas</p>
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Estimated Population</label>
                    <input type="number" name="tribe_population" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="Estimated population for this region">
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Voting Patterns (JSON - optional)</label>
                    <textarea name="voting_patterns" rows="4"
                              class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                              placeholder='[{"candidate":"Ruto","year":2022,"votes":45000}]'></textarea>
                </div>
            </div>

            <div class="mt-12 flex gap-4">
                <a href="{{ route('blocs.index') }}" 
                   class="flex-1 py-4 text-center border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">
                    Cancel
                </a>
                <button type="submit" 
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium">
                    Create Bloc
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

