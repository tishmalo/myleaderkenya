@extends('layouts.app')
@section('page_title', 'New Token Rate')
@section('content')
<form method="POST" action="{{ route('candidate-token-rates.store') }}" class="max-w-4xl mx-auto bg-zinc-900 border border-zinc-800 rounded-3xl p-8">@csrf @include('candidate-token-rates._form')<div class="mt-8 flex gap-3"><button class="bg-emerald-600 px-6 py-3 rounded-2xl">Save Rate</button><a href="{{ route('candidate-token-rates.index') }}" class="border border-zinc-700 px-6 py-3 rounded-2xl">Cancel</a></div></form>
@endsection
