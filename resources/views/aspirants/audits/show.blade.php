@extends('layouts.aspirant')
@section('content')<div class="min-h-screen p-6 lg:p-10"><div class="mx-auto flex max-w-7xl gap-8">@include('components.aspirant-sidebar')<main class="min-w-0 flex-1">@include('components.audit-detail',['back'=>route('aspirant.audits.index'),'actor'=>$auditService->actorLabel($audit->user,true)])</main></div></div>@endsection
