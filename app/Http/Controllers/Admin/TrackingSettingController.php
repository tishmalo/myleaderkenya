<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrackingSettingRequest;
use App\Services\Web\TrackingScriptService;
use App\Support\TrackingSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrackingSettingController extends Controller
{
    public function __construct(
        private TrackingScriptService $tracking
    ) {}

    public function index(): View
    {
        $settings = $this->tracking->settings();

        return view('admin.tracking', [
            'settings' => $settings,
            'configured' => TrackingSettings::isConfigured($settings),
            'previewMarkup' => TrackingSettings::headMarkup($settings),
        ]);
    }

    public function update(TrackingSettingRequest $request): RedirectResponse
    {
        $this->tracking->update($request->validated());

        return back()->with('success', 'Tracking and ads settings updated successfully.');
    }
}
