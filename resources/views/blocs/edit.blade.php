@extends('layouts.app')

@section('page_title', 'Edit Bloc')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-10">
        <h1 class="text-3xl font-semibold mb-8">Edit Bloc: {{ $bloc->name }}</h1>

        <form action="{{ route('blocs.update', $bloc) }}" method="POST">
            @csrf @method('PUT')

            <div class="space-y-8">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Bloc Name</label>
                    <input type="text" name="name" value="{{ old('name', $bloc->name) }}" required 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Tribes (comma separated)</label>
                    <input type="text" name="tribes" value="{{ old('tribes', implode(', ', $bloc->tribes ?? [])) }}" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Tribe Population</label>
                    <input type="number" name="tribe_population" value="{{ old('tribe_population', $bloc->tribe_population) }}" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Voting Patterns (JSON)</label>
                    <textarea name="voting_patterns" rows="5" 
                              class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">{{ old('voting_patterns', json_encode($bloc->voting_patterns ?? [])) }}</textarea>
                </div>
            </div>

            <div class="mt-12 flex gap-4">
                <a href="{{ route('blocs.index') }}" class="flex-1 py-4 text-center border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">Cancel</a>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium">Update Bloc</button>
            </div>
        </form>
    </div>
</div>
@endsection

