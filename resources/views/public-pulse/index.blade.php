@extends('layouts.admin')
@section('title', 'Public Pulse')
@section('content')
<div class="p-6 space-y-6">
  <div class="flex items-center justify-between"><div><h1 class="text-2xl font-bold">Public Pulse Engine Jobs</h1><p class="text-sm text-gray-500">Laravel stores requests and summaries; raw mentions remain in Pulse Engine.</p></div><div class="space-x-3"><a class="text-blue-600" href="{{ route('public-pulse.legacy') }}">Legacy Mentions</a><a class="text-blue-600" href="{{ route('public-pulse.x-sessions.index') }}">X Sessions</a></div></div>
  @if(session('success'))<div class="rounded bg-green-100 p-3 text-green-800">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="rounded bg-red-100 p-3 text-red-800">{{ session('error') }}</div>@endif
  <div class="grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ route('public-pulse.jobs.store') }}" class="rounded bg-white p-5 shadow space-y-4">@csrf
      <h2 class="font-semibold">Submit MVP job</h2>
      <label class="block text-sm">Candidate<select name="candidate_id" required class="mt-1 w-full rounded border-gray-300"><option value="">Select candidate</option>@foreach($candidates as $candidate)<option value="{{ $candidate->id }}" @selected(old('candidate_id')==$candidate->id)>{{ $candidate->name }}</option>@endforeach</select></label>
      <label class="block text-sm">Extra keywords (comma separated)<input name="keywords_text" value="{{ old('keywords_text') }}" class="mt-1 w-full rounded border-gray-300" placeholder="nickname, slogan"></label>
      <div class="grid grid-cols-2 gap-3"><label class="text-sm">From<input type="date" name="date_from" value="{{ old('date_from', now()->subDays(7)->toDateString()) }}" required class="mt-1 w-full rounded border-gray-300"></label><label class="text-sm">To<input type="date" name="date_to" value="{{ old('date_to', now()->toDateString()) }}" required class="mt-1 w-full rounded border-gray-300"></label></div>
      <label class="block text-sm">Mention limit<input type="number" min="1" max="1000" name="limit" value="{{ old('limit',20) }}" required class="mt-1 w-full rounded border-gray-300"></label>
      @if($errors->any())<ul class="text-sm text-red-600">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
      <button class="rounded bg-blue-600 px-4 py-2 text-white">Submit job</button>
    </form>
    <div class="lg:col-span-2 space-y-4">
      <form class="grid grid-cols-2 gap-3 rounded bg-white p-4 shadow md:grid-cols-5">
        <select name="candidate_id" class="rounded border-gray-300"><option value="">All candidates</option>@foreach($candidates as $candidate)<option value="{{ $candidate->id }}" @selected(($filters['candidate_id']??null)==$candidate->id)>{{ $candidate->name }}</option>@endforeach</select>
        <select name="status" class="rounded border-gray-300"><option value="">All statuses</option>@foreach(['submitting','submission_failed','queued_pending_capacity','queued','running','degraded','completed','failed'] as $status)<option @selected(($filters['status']??null)===$status)>{{ $status }}</option>@endforeach</select>
        <input type="date" name="date_from" value="{{ $filters['date_from']??'' }}" class="rounded border-gray-300"><input type="date" name="date_to" value="{{ $filters['date_to']??'' }}" class="rounded border-gray-300"><button class="rounded bg-gray-800 px-3 text-white">Filter</button>
      </form>
      <div class="overflow-x-auto rounded bg-white shadow"><table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="p-3 text-left">Candidate</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">Range / limit</th><th class="p-3 text-left">Summary</th><th class="p-3"></th></tr></thead><tbody>
      @forelse($jobs as $job)<tr class="border-t"><td class="p-3">{{ $job->candidate?->name }}</td><td class="p-3"><span class="rounded bg-gray-100 px-2 py-1">{{ $job->status }}</span>@if($job->partial)<span class="text-amber-600"> partial</span>@endif</td><td class="p-3">{{ $job->date_from?->format('Y-m-d') }} – {{ $job->date_to?->format('Y-m-d') }}<br>{{ $job->requested_limit }}</td><td class="p-3">{{ data_get($job->summary,'overall_sentiment',data_get($job->summary,'sentiment','—')) }}<br><span class="text-gray-500">confidence {{ data_get($job->summary,'confidence','—') }}</span></td><td class="p-3 text-right"><a class="text-blue-600" href="{{ route('public-pulse.jobs.show',$job) }}">Open</a></td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-gray-500">No Pulse Engine jobs yet.</td></tr>@endforelse
      </tbody></table></div>{{ $jobs->withQueryString()->links() }}
    </div>
  </div>
</div>
<script>document.querySelector('form[action="{{ route('public-pulse.jobs.store') }}"]')?.addEventListener('submit',e=>{const f=e.currentTarget,t=f.querySelector('[name=keywords_text]');t.value.split(',').map(v=>v.trim()).filter(Boolean).forEach(v=>{const i=document.createElement('input');i.type='hidden';i.name='keywords[]';i.value=v;f.appendChild(i)});t.removeAttribute('name')});</script>
@endsection
