@extends('layouts.app')

@section('page_title', 'Create Constituency')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-10">
        <h1 class="text-3xl font-semibold mb-8">Create New Constituency</h1>

        <form action="{{ route('constituencies.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-8">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                    <p class="mt-2 text-xs text-zinc-500">Upload a JPG, PNG, or WebP image for public location cards.</p>
                    @error('image')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Constituency Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    @error('name')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">County <span class="text-red-400">*</span></label>
                    <select name="county_id" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                        <option value="">Select County</option>
                        @foreach($counties as $county)
                            <option value="{{ $county->id }}" {{ old('county_id') == $county->id ? 'selected' : '' }}>
                                {{ $county->name }} ({{ $county->bloc->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    @error('county_id')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Population <span class="text-red-400">*</span></label>
                        <input type="number" name="population" value="{{ old('population') }}" required min="0"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                        @error('population')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Number of Seats <span class="text-red-400">*</span></label>
                        <input type="number" name="number_of_seats" value="{{ old('number_of_seats', 1) }}" min="1" required 
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                        @error('number_of_seats')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Registered Voters <span class="text-red-400">*</span></label>
                    <input type="number" 
                           name="registered_voters" 
                           value="{{ old('registered_voters') }}"
                           required
                           min="0"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="Enter total registered voters">
                    @error('registered_voters')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Position Name (Optional)</label>
                    <input type="text" name="position_name" value="{{ old('position_name') }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4"
                           placeholder="Member of Parliament">
                    @error('position_name')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
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