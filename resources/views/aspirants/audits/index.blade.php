@extends('layouts.aspirant')
@section('content')
<div class="min-h-screen p-6 lg:p-10"><div class="mx-auto flex max-w-7xl gap-8">@include('components.aspirant-sidebar')<main class="min-w-0 flex-1"><div class="mb-8"><p class="font-bold uppercase tracking-[.25em] text-emerald-400">{{ $candidate->name }}</p><h1 class="mt-2 text-4xl font-bold">Audit History</h1><p class="mt-2 text-zinc-400">Activity affecting your aspirant workspace.</p></div>@include('components.audit-table',['aspirantView'=>true])</main></div></div>
@endsection
