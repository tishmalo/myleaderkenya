<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignWebsiteSample;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignWebsiteSampleController extends Controller
{
    public function index()
    {
        $samples = CampaignWebsiteSample::ordered()->get();

        return view('campaign-websites.samples.index', compact('samples'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'preview_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:draft,published'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('preview_image')) {
            $file = $request->file('preview_image');
            $directory = public_path('storage/campaign-website-samples');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $filename = (string) Str::uuid() . '.' . $file->extension();
            $file->move($directory, $filename);
            $validated['image_url'] = asset('storage/campaign-website-samples/' . $filename);
        }

        unset($validated['preview_image']);

        CampaignWebsiteSample::create($validated);

        return redirect()->route('campaign-website-samples.index')
            ->with('success', 'Website sample added.');
    }

    public function destroy(CampaignWebsiteSample $campaignWebsiteSample): RedirectResponse
    {
        $path = parse_url((string) $campaignWebsiteSample->image_url, PHP_URL_PATH);

        if ($path && str_starts_with($path, '/storage/campaign-website-samples/')) {
            $file = public_path(ltrim($path, '/'));

            if (is_file($file)) {
                unlink($file);
            }
        }

        $campaignWebsiteSample->delete();

        return redirect()->route('campaign-website-samples.index')
            ->with('success', 'Website sample removed.');
    }
}
