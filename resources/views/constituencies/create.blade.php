@extends('layouts.app')

@section('page_title', 'Create Constituency')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-10">
        <h1 class="text-3xl font-semibold mb-8">Create New Constituency</h1>

        <form action="{{ route('constituencies.store') }}" method="POST">
            @csrf

            <div class="space-y-8">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Constituency Name</label>
                    <input type="text" name="name" required 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">County</label>
                    <select name="county_id" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                        <option value="">Select County</option>
                        @foreach($counties as $county)
                            <option value="{{ $county->id }}">{{ $county->name }} ({{ $county->bloc->name ?? '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Population</label>
                        <input type="number" name="population" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Number of Seats</label>
                        <input type="number" name="number_of_seats" value="1" min="1" required 
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <div>
    <label class="block text-sm text-zinc-400 mb-2">Registered Voters</label>
    <input type="number" 
           name="registered_voters" 
           value="{{ old('registered_voters') }}"
           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
           placeholder="Enter total registered voters">
</div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Position Name (Optional)</label>
                    <input type="text" name="position_name" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4"
                           placeholder="Member of Parliament">
                </div>
            </div>

            <div class="mt-12 flex gap-4">
                <a href="{{ route('constituencies.index') }}" class="flex-1 py-4 text-center border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">Cancel</a>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium">Create Constituency</button>
            </div>
        </form>
    </div>
</div>
@endsection