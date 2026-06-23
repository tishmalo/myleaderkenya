@extends('layouts.app')

@section('page_title', 'Edit Ward')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-10">
        <h1 class="text-3xl font-semibold mb-8">Edit Ward: {{ $ward->name }}</h1>

        <form action="{{ route('wards.update', $ward) }}" method="POST">
            @csrf @method('PUT')

            <div class="space-y-8">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Ward Name</label>
                    <input type="text" name="name" value="{{ old('name', $ward->name) }}" required 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Constituency</label>
                    <div class="relative">
                        <input type="text" id="constituency-search" placeholder="Search constituency..."
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 mb-2">
                        <select name="constituency_id" id="constituency-select" required
                                class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4" size="8">
                            @foreach($constituencies as $constituency)
                                <option value="{{ $constituency->id }}"
                                        {{ $constituency->id == $ward->constituency_id ? 'selected' : '' }}
                                        data-name="{{ strtolower($constituency->name) }}"
                                        data-county="{{ strtolower($constituency->county->name ?? '') }}">
                                    {{ $constituency->name }} ({{ $constituency->county->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Population</label>
                        <input type="number" name="population" value="{{ old('population', $ward->population) }}" 
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Registered Voters</label>
                        <input type="number" name="registered_voters" value="{{ old('registered_voters', $ward->registered_voters) }}" 
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4">
                    </div>
                </div>
            </div>

            <div class="mt-12 flex gap-4">
                <a href="{{ route('wards.index') }}" class="flex-1 py-4 text-center border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">Cancel</a>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium">Update Ward</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('constituency-search');
    const select = document.getElementById('constituency-select');
    const options = Array.from(select.options);

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();

        options.forEach(option => {
            const name = option.getAttribute('data-name') || '';
            const county = option.getAttribute('data-county') || '';

            if (name.includes(searchTerm) || county.includes(searchTerm)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });

        // Show first matching option
        const firstVisible = options.find(opt => opt.style.display !== 'none');
        if (firstVisible) {
            select.scrollTop = 0;
        }
    });

    // Clear search when selecting
    select.addEventListener('change', function() {
        if (this.value) {
            searchInput.value = '';
            options.forEach(opt => opt.style.display = '');
        }
    });
});
</script>
@endpush

@endsection