@extends('layouts.app')

@section('page_title', 'Create County')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-10">
        <h1 class="text-3xl font-semibold mb-8">Create New County</h1>

        <form action="{{ route('counties.store') }}" method="POST">
            @csrf

            <div class="space-y-8">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">County Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    @error('name')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-3">Blocs</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-72 overflow-y-auto rounded-2xl border border-zinc-800 bg-zinc-950 p-4">
                        @foreach($blocs as $bloc)
                            <label class="flex items-center gap-3 rounded-xl bg-zinc-900 px-4 py-3 text-sm text-zinc-200">
                                <input type="checkbox" name="bloc_ids[]" value="{{ $bloc->id }}" class="rounded border-zinc-600 bg-zinc-800 text-emerald-500"
                                       {{ in_array($bloc->id, old('bloc_ids', [])) ? 'checked' : '' }}>
                                <span>{{ $bloc->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-zinc-500 mt-2">The first selected bloc is saved as the legacy primary bloc.</p>
                    @error('bloc_ids')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Area (sq km)</label>
                        <input type="number" name="area" value="{{ old('area') }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Capital</label>
                        <input type="text" name="capital" value="{{ old('capital') }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Population</label>
                        <input type="number" name="population" value="{{ old('population') }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Registered Voters</label>
                        <input type="number" name="registered_voters" value="{{ old('registered_voters') }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Postal Abbreviation</label>
                    <input type="text" name="postal_abbreviation" value="{{ old('postal_abbreviation') }}" maxlength="10"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                </div>
            </div>

            <div class="mt-12 flex gap-4">
                <a href="{{ route('counties.index') }}" class="flex-1 py-4 text-center border border-zinc-700 rounded-2xl font-medium">Cancel</a>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium">Create County</button>
            </div>
        </form>
    </div>
</div>
@endsection