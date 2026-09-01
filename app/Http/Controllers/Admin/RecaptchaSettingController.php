<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecaptchaSettingRequest;
use App\Services\Admin\SettingService;

class RecaptchaSettingController extends Controller
{
    public function __construct(
        private SettingService $settingService
    ) {}

    public function index()
    {
        $settings = $this->settingService->getRecaptchaSettings();

        return view('admin.recaptcha', $settings);
    }

    public function update(RecaptchaSettingRequest $request)
    {
        $this->settingService->updateRecaptchaSettings($request->validated());

        return back()->with('success', 'reCAPTCHA settings updated successfully.');
    }
}
