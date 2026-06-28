@extends('layouts.app')

@section('page_title', 'Political Parties')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white"><i class="fas fa-landmark text-emerald-500"></i> Political Parties</h1>
        <a href="{{ route('political-parties.create') }}" class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2"><i class="fas fa-plus"></i> New Political Party</a>
    </div>

    <form method="GET" action="{{ route('political-parties.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-[1fr_180px_auto] gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search political parties" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white focus:outline-none focus:border-emerald-500">
        <select name="status" class="bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-3 text-white">
            <option value="">All Statuses</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        <button type="submit" class="bg-zinc-700 hover:bg-zinc-600 px-6 py-3 rounded-2xl text-sm font-medium">Filter</button>
    </form>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950"><tr><th class="px-6 py-4 text-left">Party</th><th class="px-6 py-4 text-left">URL</th><th class="px-6 py-4 text-center">Order</th><th class="px-6 py-4 text-center">Status</th><th class="px-6 py-4 text-center">Actions</th></tr></thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($politicalParties as $party)
                <tr class="hover:bg-zinc-800/70">
                    <td class="px-6 py-4"><div class="flex items-center gap-4">
                        @if($party->logo)<img src="{{ Storage::url($party->logo) }}" class="w-12 h-12 object-cover rounded-xl" alt="{{ $party->name }}">@else<div class="w-12 h-12 rounded-xl bg-zinc-800 flex items-center justify-center text-emerald-400"><i class="fas fa-landmark"></i></div>@endif
                        <div><p class="font-medium text-white">{{ $party->name }}</p><p class="text-xs text-zinc-500">{{ $party->abbreviation ?: 'No abbreviation' }}</p></div>
                    </div></td>
                    <td class="px-6 py-4 text-sm text-zinc-400"><a href="{{ route('parties.show', $party->slug) }}" class="hover:text-emerald-400" target="_blank">/parties/{{ $party->slug }}</a></td>
                    <td class="px-6 py-4 text-center text-zinc-400">{{ $party->sort_order }}</td>
                    <td class="px-6 py-4 text-center">@if($party->status === 'published')<span class="px-3 py-1 text-xs font-medium rounded-full bg-emerald-500/20 text-emerald-400">Published</span>@else<span class="px-3 py-1 text-xs font-medium rounded-full bg-orange-500/20 text-orange-400">Draft</span>@endif</td>
                    <td class="px-6 py-4 text-center"><a href="{{ route('political-parties.edit', $party) }}" class="text-blue-400 hover:text-blue-500 mx-2"><i class="fas fa-edit"></i></a><button onclick="deletePoliticalParty('{{ $party->slug }}', '{{ addslashes($party->name) }}')" class="text-red-400 hover:text-red-500 mx-2"><i class="fas fa-trash"></i></button></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-16 text-zinc-500">No political parties found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-center">{{ $politicalParties->appends(request()->query())->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
function deletePoliticalParty(slug, name) { showDeleteModal(`/admin/political-parties/${slug}`, `Delete political party <strong>${name}</strong>?`); }
</script>
@endpush
