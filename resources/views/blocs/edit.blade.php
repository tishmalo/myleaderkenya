@extends('layouts.app')

@section('page_title', 'Edit Bloc')

@section('content')
@php
    $selectedCountyIds = old('county_ids', $bloc->counties->pluck('id')->all());
@endphp
<div class="max-w-4xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-10">
        <h1 class="text-3xl font-semibold mb-8">Edit Bloc: {{ $bloc->name }}</h1>

        <form action="{{ route('blocs.update', $bloc) }}" method="POST">
            @csrf @method('PUT')

            <div class="space-y-8">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Bloc Name</label>
                    <input type="text" name="name" value="{{ old('name', $bloc->name) }}" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                    @error('name')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Bloc Type</label>
                        <select name="type" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                            @foreach($blocTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $bloc->type ?? 'economic') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Tribe Population</label>
                        <input type="number" name="tribe_population" value="{{ old('tribe_population', $bloc->tribe_population) }}"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">{{ old('description', $bloc->description) }}</textarea>
                    @error('description')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-3">Assigned Counties</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-96 overflow-y-auto rounded-2xl border border-zinc-800 bg-zinc-950 p-4">
                        @foreach($counties as $county)
                            <label class="flex items-center gap-3 rounded-xl bg-zinc-900 px-4 py-3 text-sm text-zinc-200">
                                <input type="checkbox" name="county_ids[]" value="{{ $county->id }}" class="rounded border-zinc-600 bg-zinc-800 text-emerald-500"
                                       {{ in_array($county->id, $selectedCountyIds) ? 'checked' : '' }}>
                                <span>{{ $county->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('county_ids')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Tribes (comma separated)</label>
                        <input type="text" name="tribes" value="{{ old('tribes', implode(', ', $bloc->tribes ?? [])) }}"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Voting Patterns (JSON)</label>
                        <textarea name="voting_patterns" rows="3" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">{{ old('voting_patterns', json_encode($bloc->voting_patterns ?? [])) }}</textarea>
                    </div>
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