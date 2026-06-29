@extends('layouts.app')

@section('page_title', 'Live Stat Figures')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold flex items-center gap-3 text-white">
            <i class="fas fa-sliders text-emerald-500"></i>
            Live Stat Figures
        </h1>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-200">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <form method="POST" action="{{ route('live-stat-figures.store') }}" class="lg:col-span-1 bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            @csrf
            <h2 class="text-xl font-semibold text-white mb-5">Generate Figures</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Batch Name</label>
                    <input type="text" name="batch_name" value="{{ old('batch_name') }}" placeholder="Launch traction seed"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white placeholder:text-zinc-500 focus:outline-none focus:border-emerald-500">
                </div>

                @foreach($metrics as $key => $label)
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">{{ $label }}</label>
                        <input type="number" min="0" name="figures[{{ $key }}]" value="{{ old('figures.' . $key, 0) }}"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                @endforeach

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Notes</label>
                    <textarea name="notes" rows="3" placeholder="Why these figures exist"
                              class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white placeholder:text-zinc-500 focus:outline-none focus:border-emerald-500">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 rounded-2xl px-5 py-3 font-semibold text-white">
                    Generate Identifiable Batch
                </button>
            </div>
        </form>

        <div class="lg:col-span-2 bg-zinc-900 border border-zinc-800 rounded-3xl p-6">
            <h2 class="text-xl font-semibold text-white mb-5">Generated Batches</h2>
            <div class="space-y-3">
                @forelse($batches as $batch)
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-800 bg-zinc-950/50 p-4">
                        <div>
                            <div class="font-semibold text-white">{{ $batch->batch_name ?? $batch->batch_id }}</div>
                            <div class="text-sm text-zinc-500">ID: {{ $batch->batch_id }} · {{ $batch->figures_count }} figure(s) · Total {{ number_format($batch->total_value) }}</div>
                        </div>
                        <form method="POST" action="{{ route('live-stat-figures.batches.destroy', $batch->batch_id) }}" onsubmit="return confirm('Delete this entire generated batch?')">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-xl bg-red-500/10 px-4 py-2 text-sm font-semibold text-red-300 hover:bg-red-500/20">Delete Batch</button>
                        </form>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-zinc-700 p-8 text-center text-zinc-500">No generated batches yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-zinc-950">
                <tr class="border-b border-zinc-800">
                    <th class="px-6 py-4 text-left">Metric</th>
                    <th class="px-6 py-4 text-left">Value</th>
                    <th class="px-6 py-4 text-left">Source</th>
                    <th class="px-6 py-4 text-left">Batch ID</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($figures as $figure)
                    <tr>
                        <td class="px-6 py-4 text-white">{{ $figure->label }}</td>
                        <td class="px-6 py-4 font-mono text-emerald-300">{{ number_format($figure->value) }}</td>
                        <td class="px-6 py-4 text-zinc-400">{{ ucfirst($figure->source) }}</td>
                        <td class="px-6 py-4 text-xs text-zinc-500">{{ $figure->batch_id ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $figure->active ? 'Active' : 'Inactive' }}</td>
                        <td class="px-6 py-4 text-center">
                            <form method="POST" action="{{ route('live-stat-figures.destroy', $figure) }}" onsubmit="return confirm('Delete this figure?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-400 hover:text-red-300"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-16 text-center text-zinc-500">No figures created yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-center">{{ $figures->links() }}</div>
</div>
@endsection
