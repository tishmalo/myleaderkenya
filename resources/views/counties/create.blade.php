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
                    <input type="text" name="name" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Bloc</label>
                    <select name="bloc_id" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                        <option value="">Select Bloc</option>
                        @foreach($blocs as $bloc)
                            <option value="{{ $bloc->id }}">{{ $bloc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Area (sq km)</label>
                        <input type="number" name="area" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Capital</label>
                        <input type="text" name="capital" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Population</label>
                        <input type="number" name="population" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Registered Voters</label>
                        <input type="number" name="registered_voters" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Postal Abbreviation</label>
                    <input type="text" name="postal_abbreviation" maxlength="10" 
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