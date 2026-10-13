@extends('layouts.app')

@section('page_title', 'Tracking & Ads')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto">
        <div class="bg-zinc-900 border border-zinc-800 overflow-hidden shadow-sm rounded-3xl">
            <div class="p-8 text-zinc-100">
                @if(session('success'))
                    <div class="mb-6 p-4 text-sm font-medium text-emerald-400 bg-emerald-500/10 rounded-2xl border border-emerald-500/20">
                        {{ session('success') }}
                    </div>
                @endif

                <h1 class="text-2xl font-semibold text-white">Tracking &amp; Ads</h1>
                <p class="text-sm text-zinc-500 mt-1">
                    Add Google Analytics, Google Tag Manager, Google AdSense, the Meta pixel and any other head code.
                    Everything here is injected into the &lt;head&gt; of the pages you switch on below — no deployment needed.
                </p>

                @if($configured)
                    <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        {{ $settings['tracking_public'] === '1' ? 'Active on the public site' : 'Public site switched off' }}
                        &middot;
                        {{ $settings['tracking_admin'] === '1' ? 'dashboards on' : 'dashboards off' }}
                    </div>
                @else
                    <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-400 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span> Nothing configured yet
                    </div>
                @endif

                <form action="{{ route('admin.tracking.update') }}" method="POST" class="space-y-6 mt-6">
                    @csrf

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-6">
                        <h2 class="text-sm font-semibold text-zinc-200">Where the code loads</h2>
                        <p class="text-xs text-zinc-500 mt-1">Switch each area on or off independently.</p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <label class="flex items-start gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="tracking_public" value="1" @checked($settings['tracking_public'] === '1')
                                       class="mt-1 h-4 w-4 rounded border-zinc-600 bg-zinc-900 text-emerald-500 focus:ring-emerald-500">
                                <span>
                                    <span class="block text-sm font-medium text-zinc-300">Public website</span>
                                    <span class="block text-xs text-zinc-500 mt-1">Landing page, aspirants, counties, news, polls and every other public page.</span>
                                </span>
                            </label>

                            <label class="flex items-start gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="tracking_admin" value="1" @checked($settings['tracking_admin'] === '1')
                                       class="mt-1 h-4 w-4 rounded border-zinc-600 bg-zinc-900 text-emerald-500 focus:ring-emerald-500">
                                <span>
                                    <span class="block text-sm font-medium text-zinc-300">Admin &amp; aspirant dashboards</span>
                                    <span class="block text-xs text-zinc-500 mt-1">Usually left off, so staff activity is not mixed into your public analytics.</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="google_analytics_id" class="block text-sm font-medium text-zinc-300">Google Analytics measurement ID</label>
                            <input type="text" id="google_analytics_id" name="google_analytics_id" value="{{ old('google_analytics_id', $settings['google_analytics_id']) }}" placeholder="G-ABC123XYZ4" autocomplete="off"
                                   class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                            <p class="text-xs text-zinc-500 mt-2">Analytics &rarr; Admin &rarr; Data streams. Adds the gtag.js snippet. The <code>G-</code> is added for you.</p>
                            @error('google_analytics_id') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="google_tag_manager_id" class="block text-sm font-medium text-zinc-300">Google Tag Manager container ID</label>
                            <input type="text" id="google_tag_manager_id" name="google_tag_manager_id" value="{{ old('google_tag_manager_id', $settings['google_tag_manager_id']) }}" placeholder="GTM-ABC1234" autocomplete="off"
                                   class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                            <p class="text-xs text-zinc-500 mt-2">Tag Manager &rarr; your container. Loads every tag you publish there.</p>
                            @error('google_tag_manager_id') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="google_adsense_client" class="block text-sm font-medium text-zinc-300">Google AdSense publisher ID</label>
                            <input type="text" id="google_adsense_client" name="google_adsense_client" value="{{ old('google_adsense_client', $settings['google_adsense_client']) }}" placeholder="ca-pub-1234567890123456" autocomplete="off"
                                   class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                            <p class="text-xs text-zinc-500 mt-2">AdSense &rarr; Account &rarr; Account information. Loads the AdSense script and the account meta tag.</p>
                            @error('google_adsense_client') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror

                            <label class="mt-3 flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="google_adsense_auto_ads" value="1" @checked($settings['google_adsense_auto_ads'] === '1')
                                       class="h-4 w-4 rounded border-zinc-600 bg-zinc-900 text-emerald-500 focus:ring-emerald-500">
                                <span class="text-sm text-zinc-300">Ask AdSense for Auto ads</span>
                            </label>
                            <p class="text-xs text-zinc-500 mt-1">Only needed if Auto ads are switched on for this site in AdSense.</p>
                        </div>

                        <div>
                            <label for="facebook_pixel_id" class="block text-sm font-medium text-zinc-300">Meta (Facebook) pixel ID</label>
                            <input type="text" id="facebook_pixel_id" name="facebook_pixel_id" value="{{ old('facebook_pixel_id', $settings['facebook_pixel_id']) }}" placeholder="123456789012345" autocomplete="off"
                                   class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4">
                            <p class="text-xs text-zinc-500 mt-2">Events Manager &rarr; Data sources. Adds the pixel and a PageView event. Digits only.</p>
                            @error('facebook_pixel_id') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="head_scripts" class="block text-sm font-medium text-zinc-300">Any other head code</label>
                        <textarea id="head_scripts" name="head_scripts" rows="6" spellcheck="false" placeholder="&lt;script&gt;...&lt;/script&gt;"
                                  class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4 font-mono text-xs">{{ old('head_scripts', $settings['head_scripts']) }}</textarea>
                        <p class="text-xs text-zinc-500 mt-2">
                            Pasted exactly as written, after the snippets above - use it for TikTok, Hotjar, Search Console verification and anything else.
                        </p>
                        @error('head_scripts') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-xs text-zinc-500 leading-relaxed">
                        <p class="font-semibold text-zinc-400 mb-1">Before you save</p>
                        <p>&bull; Anything saved here runs in the browser of every visitor, so only paste code from a source you trust.</p>
                        <p>&bull; Test with the vendor's own tools (Analytics Realtime, Tag Assistant, AdSense preview) after saving.</p>
                        <p>&bull; Clear the field and save to remove a snippet again.</p>
                    </div>

                    @if($previewMarkup !== '')
                        <details class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4">
                            <summary class="cursor-pointer text-sm font-medium text-zinc-300">Show the code that will be added to &lt;head&gt;</summary>
                            <pre class="mt-3 whitespace-pre-wrap break-all text-xs text-zinc-400 font-mono">{{ $previewMarkup }}</pre>
                        </details>
                    @endif

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-medium text-white hover:bg-emerald-700">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
