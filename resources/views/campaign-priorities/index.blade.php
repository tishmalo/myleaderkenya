@extends('layouts.app')

@section('page_title', 'Campaign Priorities')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.2em] text-emerald-400">Aspirant content</p>
            <h1 class="mt-2 text-3xl font-semibold text-white">Campaign Priorities</h1>
            <p class="mt-2 text-sm text-zinc-400">Define the groups aspirants can select, then review their manifesto statements before publication.</p>
        </div>
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900 px-5 py-4 text-sm text-zinc-300">
            <strong class="block text-2xl text-white">{{ $submissions->total() }}</strong> filtered submissions
        </div>
    </div>

    @if(session('success'))<div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-200"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <section class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
        <div class="mb-5"><h2 class="text-xl font-semibold text-white">Add priority group</h2><p class="mt-1 text-sm text-zinc-500">Icons are selected from a controlled allowlist; custom HTML is never accepted.</p></div>
        <form method="POST" action="{{ route('campaign-priority-categories.store') }}" class="grid gap-4 lg:grid-cols-5">@csrf
            <div class="lg:col-span-2"><label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Name</label><input name="name" value="{{ old('name') }}" required maxlength="120" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white"></div>
            <div><label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Icon</label><select name="icon" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white">@foreach(\App\Models\CampaignPriorityCategory::ICONS as $icon)<option value="{{ $icon }}" @selected(old('icon')===$icon)>{{ str_replace(['fas fa-', '-'], ['', ' '], $icon) }}</option>@endforeach</select></div>
            <div><label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Order</label><input type="number" name="sort_order" value="{{ old('sort_order', 50) }}" min="0" max="10000" required class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white"></div>
            <div class="flex items-end"><input type="hidden" name="is_active" value="1"><button class="w-full rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white hover:bg-emerald-500"><i class="fas fa-plus mr-2"></i>Add group</button></div>
            <div class="lg:col-span-5"><label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Description</label><input name="description" value="{{ old('description') }}" maxlength="500" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white"></div>
        </form>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        @foreach($categories as $category)
        <article class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
            <form method="POST" action="{{ route('campaign-priority-categories.update', $category) }}" class="space-y-4">@csrf @method('PUT')
                <div class="flex items-center gap-4"><div class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-500/10 text-xl text-emerald-400"><i class="{{ $category->icon }}"></i></div><div><strong class="text-white">{{ $category->name }}</strong><span class="ml-2 rounded-full px-2 py-1 text-[10px] font-bold uppercase {{ $category->is_active ? 'bg-emerald-500/10 text-emerald-300' : 'bg-zinc-800 text-zinc-500' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span><p class="mt-1 text-xs text-zinc-500">Used by {{ $category->candidate_priorities_count }} aspirant(s)</p></div></div>
                <div class="grid gap-3 md:grid-cols-2"><input name="name" value="{{ $category->name }}" required maxlength="120" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white"><select name="icon" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white">@foreach(\App\Models\CampaignPriorityCategory::ICONS as $icon)<option value="{{ $icon }}" @selected($category->icon===$icon)>{{ str_replace(['fas fa-', '-'], ['', ' '], $icon) }}</option>@endforeach</select></div>
                <input name="description" value="{{ $category->description }}" maxlength="500" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white" placeholder="Short description">
                <div class="grid gap-3 md:grid-cols-3"><input type="number" name="sort_order" value="{{ $category->sort_order }}" min="0" max="10000" required class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white"><select name="is_active" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white"><option value="1" @selected($category->is_active)>Active</option><option value="0" @selected(!$category->is_active)>Inactive</option></select><button class="rounded-xl border border-emerald-500/30 px-4 py-3 font-semibold text-emerald-300">Save changes</button></div>
            </form>
            @if($category->candidate_priorities_count===0)<form method="POST" action="{{ route('campaign-priority-categories.destroy', $category) }}" class="mt-3 text-right">@csrf @method('DELETE')<button class="text-xs font-semibold text-red-400" onclick="return confirm('Delete this unused category?')">Delete unused group</button></form>@endif
        </article>
        @endforeach
    </section>

    <section class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
        <div class="flex flex-wrap items-end justify-between gap-4"><div><h2 class="text-xl font-semibold text-white">Manifesto review queue</h2><p class="mt-1 text-sm text-zinc-500">Only approved entries are visible on public profiles.</p></div><form class="flex flex-wrap gap-2"><input name="candidate" value="{{ request('candidate') }}" placeholder="Candidate name" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-2 text-white"><select name="status" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-2 text-white"><option value="">All states</option>@foreach(['pending','approved','rejected'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="rounded-xl bg-zinc-800 px-4 py-2 text-white">Filter</button></form></div>
        <div class="mt-6 space-y-3">
            @forelse($submissions as $submission)
            <article class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                <div class="flex flex-wrap items-start justify-between gap-4"><div class="min-w-0"><div class="flex items-center gap-3"><i class="{{ $submission->category?->icon }} text-emerald-400"></i><strong class="text-white">{{ $submission->category?->name }}</strong><span class="rounded-full px-2 py-1 text-[10px] font-bold uppercase {{ $submission->status==='approved'?'bg-emerald-500/10 text-emerald-300':($submission->status==='rejected'?'bg-red-500/10 text-red-300':'bg-amber-500/10 text-amber-300') }}">{{ $submission->status }}</span></div><a href="{{ route('candidates.edit', $submission->candidate) }}" class="mt-2 block text-sm font-semibold text-zinc-300 hover:text-white">{{ $submission->candidate?->name }}</a><p class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-400">{{ $submission->manifesto }}</p></div><div class="flex gap-2"><form method="POST" action="{{ route('candidate-campaign-priorities.review', [$submission->candidate, $submission]) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">Approve</button></form><form method="POST" action="{{ route('candidate-campaign-priorities.review', [$submission->candidate, $submission]) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="rounded-xl bg-red-800 px-4 py-2 text-sm font-semibold text-white">Reject</button></form></div></div>
            </article>
            @empty<p class="rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-zinc-500">No manifesto submissions match these filters.</p>@endforelse
        </div>
        <div class="mt-5">{{ $submissions->links() }}</div>
    </section>
</div>
@endsection