<div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/60">
    <div class="overflow-x-auto"><table class="min-w-full text-left text-sm">
        <thead class="bg-zinc-900 text-xs uppercase tracking-wider text-zinc-500"><tr><th class="px-5 py-4">When</th><th class="px-5 py-4">Actor</th><th class="px-5 py-4">Activity</th><th class="px-5 py-4">Module</th><th class="px-5 py-4">Status</th><th class="px-5 py-4"></th></tr></thead>
        <tbody class="divide-y divide-zinc-800">
        @forelse($audits as $entry)
            <tr class="text-zinc-300"><td class="whitespace-nowrap px-5 py-4">{{ $entry->created_at?->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</td><td class="px-5 py-4">{{ $aspirantView ? $auditService->actorLabel($entry->user, true) : ($entry->user?->name ?? 'System') }}</td><td class="px-5 py-4"><strong class="text-white">{{ $entry->summary ?: str($entry->event)->headline() }}</strong><div class="text-xs text-zinc-500">{{ $entry->event }}</div></td><td class="px-5 py-4">{{ str($entry->module ?: 'system')->headline() }}</td><td class="px-5 py-4"><span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold uppercase text-emerald-400">{{ $entry->status }}</span></td><td class="px-5 py-4"><a class="text-emerald-400 hover:text-emerald-300" href="{{ $aspirantView ? route('aspirant.audits.show',$entry) : route('audits.show',$entry) }}">Details</a></td></tr>
        @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-zinc-500">No audit activity matches these filters.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
<div class="mt-6">{{ $audits->links() }}</div>
