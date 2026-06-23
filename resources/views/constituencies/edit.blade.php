@extends('layouts.app')

@section('page_title', 'Edit Constituency')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-10">
        <h1 class="text-3xl font-semibold mb-8">Edit Constituency: {{ $constituency->name }}</h1>

        <form action="{{ route('constituencies.update', $constituency) }}" method="POST">
            @csrf @method('PUT')

            <div class="space-y-8">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Constituency Name</label>
                    <input type="text" name="name" value="{{ old('name', $constituency->name) }}" required 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">County</label>
                    <select name="county_id" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                        @foreach($counties as $county)
                            <option value="{{ $county->id }}" {{ $county->id == $constituency->county_id ? 'selected' : '' }}>
                                {{ $county->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Population</label>
                        <input type="number" name="population" value="{{ old('population', $constituency->population) }}" 
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                    <div>
    <label class="block text-sm text-zinc-400 mb-2">Registered Voters</label>
    <input type="number" 
           name="registered_voters" 
           value="{{ old('registered_voters', $constituency->registered_voters) }}"
           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
           placeholder="Enter total registered voters">
</div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Number of Seats</label>
                        <input type="number" name="number_of_seats" value="{{ old('number_of_seats', $constituency->number_of_seats) }}" 
                               min="1" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Position Name (Optional)</label>
                    <input type="text" name="position_name" value="{{ old('position_name', $constituency->position_name) }}" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                </div>
            </div>

            <div class="mt-12 flex gap-4">
                <a href="{{ route('constituencies.index') }}" class="flex-1 py-4 text-center border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">Cancel</a>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium">Update Constituency</button>
            </div>
        </form>
    </div>
</div>
@endsection

