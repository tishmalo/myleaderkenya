@extends('layouts.app')

@section('page_title', 'Edit County')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-10">
        <h1 class="text-3xl font-semibold mb-8">Edit County: {{ $county->name }}</h1>

        <form action="{{ route('counties.update', $county) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-8">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Image</label>
                    @if($county->image)
                        <img src="{{ Storage::url($county->image) }}" alt="{{ $county->name }}" class="mb-3 h-36 w-full rounded-2xl object-cover border border-zinc-700">
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                    <p class="mt-2 text-xs text-zinc-500">Leave blank to keep the current county image. JPG, PNG, or WebP up to 4MB.</p>
                </div>
                
                <!-- County Name -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">County Name</label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name', $county->name) }}" 
                           required 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Bloc -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Bloc</label>
                    <select name="bloc_id" required 
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                        @foreach($blocs as $bloc)
                            <option value="{{ $bloc->id }}" 
                                    {{ $bloc->id == $county->bloc_id ? 'selected' : '' }}>
                                {{ $bloc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Area -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Area (sq km)</label>
                    <input type="text" 
                           name="area" 
                           value="{{ old('area', $county->area) }}" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="e.g. 2450">
                </div>

                <!-- Population -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Population</label>
                    <input type="number" 
                           name="population" 
                           value="{{ old('population', $county->population) }}" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="Total population">
                </div>

                <!-- Registered Voters -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Registered Voters</label>
                    <input type="number" 
                           name="registered_voters" 
                           value="{{ old('registered_voters', $county->registered_voters) }}" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="Total registered voters">
                </div>

                <!-- Capital -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Capital / Headquarters</label>
                    <input type="text" 
                           name="capital" 
                           value="{{ old('capital', $county->capital) }}" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="e.g. Kisumu City">
                </div>

                <!-- Postal Abbreviation -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Postal Abbreviation</label>
                    <input type="text" 
                           name="postal_abbreviation" 
                           value="{{ old('postal_abbreviation', $county->postal_abbreviation) }}" 
                           maxlength="10"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="e.g. KSM">
                </div>

            </div>

            <div class="mt-12 flex gap-4">
                <a href="{{ route('counties.index') }}" 
                   class="flex-1 py-4 text-center border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">
                    Cancel
                </a>
                <button type="submit" 
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium">
                    Update County
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


