@extends('layouts.app')

@section('page_title', 'Support Groups')

@section('content')
<div class="mx-auto w-full max-w-6xl">
    <div class="mb-8 flex items-center justify-between gap-4">
        <h1 class="flex items-center gap-3 text-3xl font-semibold text-white">
            <i class="fas fa-people-group text-emerald-500"></i>
            Support Groups
        </h1>
    </div>

    @foreach(['success' => 'emerald', 'warning' => 'amber', 'error' => 'red'] as $flashKey => $flashColor)
        @if(session($flashKey))
            <div class="mb-6 rounded-2xl border border-{{ $flashColor }}-700/60 bg-{{ $flashColor }}-950/50 px-5 py-4 text-{{ $flashColor }}-100">
                {{ session($flashKey) }}
            </div>
        @endif
    @endforeach

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-700/60 bg-red-950/50 px-5 py-4 text-red-100">
            {{ $errors->first() }}
        </div>
    @endif

    @if(auth()->user()?->canAccess('support-groups.create'))
        <form method="POST" action="{{ route('support-group-types.store') }}" class="mb-8 rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
            @csrf
            <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_160px_140px]">
                <div>
                    <label class="mb-2 block text-sm text-zinc-400">Group Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" maxlength="100" required placeholder="Friends, Family, Volunteers"
                           class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm text-zinc-400">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                </div>
                <div class="flex items-end">
                    <label class="inline-flex h-[50px] items-center gap-3 rounded-2xl border border-zinc-700 bg-zinc-800 px-4 text-sm text-zinc-300">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-zinc-700 bg-zinc-800 text-emerald-600" checked>
                        Active
                    </label>
                </div>
            </div>
            <button class="mt-5 rounded-2xl bg-emerald-600 px-6 py-3 font-semibold text-white hover:bg-emerald-700">
                Add Group Type
            </button>
        </form>
    @endif

    <div class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px]">
                <thead class="bg-zinc-950">
                    <tr class="border-b border-zinc-800">
                        <th class="px-6 py-4 text-left">Name</th>
                        <th class="px-6 py-4 text-left">Slug</th>
                        <th class="px-6 py-4 text-center">Contacts</th>
                        <th class="px-6 py-4 text-center">Active</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse($supportGroupTypes as $type)
                        <tr class="hover:bg-zinc-800/70">
                            <td class="px-6 py-4">
                                @if(auth()->user()?->canAccess('support-groups.update'))
                                    <form id="support-group-type-{{ $type->id }}" method="POST" action="{{ route('support-group-types.update', $type) }}" class="grid grid-cols-[1fr_100px] gap-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="name" value="{{ old('name', $type->name) }}" maxlength="100" required class="rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2 text-white">
                                        <input type="number" name="sort_order" value="{{ old('sort_order', $type->sort_order) }}" min="0" class="rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2 text-white">
                                        <input type="hidden" name="is_active" value="0">
                                    </form>
                                @else
                                    <span class="font-semibold text-white">{{ $type->name }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-zinc-400">{{ $type->slug }}</td>
                            <td class="px-6 py-4 text-center text-zinc-300">{{ number_format($type->contacts_count) }}</td>
                            <td class="px-6 py-4 text-center">
                                @if(auth()->user()?->canAccess('support-groups.update'))
                                    <input form="support-group-type-{{ $type->id }}" type="checkbox" name="is_active" value="1" class="rounded border-zinc-700 bg-zinc-800 text-emerald-600" {{ $type->is_active ? 'checked' : '' }}>
                                @else
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $type->is_active ? 'bg-emerald-900/50 text-emerald-300' : 'bg-zinc-800 text-zinc-400' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-3">
                                    @if(auth()->user()?->canAccess('support-groups.update'))
                                        <button form="support-group-type-{{ $type->id }}" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save</button>
                                    @endif
                                    @if(auth()->user()?->canAccess('support-groups.delete'))
                                        <form method="POST" action="{{ route('support-group-types.destroy', $type) }}" onsubmit="return confirm('Delete this support group type?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-xl border border-red-800 px-4 py-2 text-sm font-semibold text-red-300 hover:bg-red-950">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-zinc-500">No support group types found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
