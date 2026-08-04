<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>Account Recovery - My Leader Kenya</title><link rel="icon" href="{{ asset('images/myleader.ico') }}" type="image/x-icon">
<link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet"><script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-zinc-950 font-['Inter'] text-white antialiased">
<div class="fixed inset-x-0 top-0 z-50 h-1 bg-gradient-to-r from-green-700 via-zinc-950 to-red-600"></div>
<main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-12">
<div class="pointer-events-none absolute -left-28 top-20 h-72 w-72 rounded-full bg-green-700/10 blur-3xl"></div><div class="pointer-events-none absolute -right-28 bottom-16 h-72 w-72 rounded-full bg-red-700/10 blur-3xl"></div>
<div class="relative w-full max-w-lg">
<a href="{{ route('landing') }}" class="mb-7 flex flex-col items-center" aria-label="Back to My Leader Kenya"><span class="flex h-20 w-20 items-center justify-center rounded-2xl border border-zinc-800 bg-zinc-900 shadow-2xl"><img src="{{ asset('images/myleader.png') }}" alt="My Leader Kenya" class="h-14 w-14 object-contain"></span><span class="mt-4 text-lg font-extrabold tracking-[0.14em]">MY LEADER KENYA</span></a>
<section class="rounded-3xl border border-zinc-800 bg-zinc-900/95 p-6 shadow-2xl sm:p-9">{{ $slot }}</section>
<div class="mt-6 text-center"><a href="{{ route('landing') }}" class="text-sm font-medium text-zinc-500 transition hover:text-white">&larr; Back to website</a></div>
</div></main></body></html>
