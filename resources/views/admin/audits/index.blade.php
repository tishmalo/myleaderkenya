@extends('layouts.app')
@section('page_title','Audit Log')
@section('content')
<div class="mb-8"><h1 class="text-3xl font-bold text-white">Audit Log</h1><p class="mt-2 text-zinc-400">Immutable system, security, and business activity.</p></div>
<form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 md:grid-cols-4"><input name="event" value="{{ request('event') }}" placeholder="Event" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3"><input name="module" value="{{ request('module') }}" placeholder="Module" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3"><input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3"><button class="rounded-xl bg-emerald-600 px-5 py-3 font-bold">Filter</button></form>
@include('components.audit-table',['aspirantView'=>false,'auditService'=>app(\App\Services\Audit\AuditService::class)])
@endsection
