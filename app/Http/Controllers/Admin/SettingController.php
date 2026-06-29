<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDonateSettingRequest;
use App\Services\Admin\SettingService;
use App\Services\Admin\PaymentMethodService;
class SettingController extends Controller
{
    public function __construct(
        private SettingService $settingService,
        private PaymentMethodService $paymentMethodService
    ) {}

    public function donateSettings()
    {
        $settings = $this->settingService->getDonateSettings();
        $paymentMethods = $this->paymentMethodService->getAllPaymentMethods();

        return view('settings.donate', [
            'donateWhyText' => $settings['donateWhyText'],
            'whatsappLink' => $settings['whatsappLink'],
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function updateDonateSettings(UpdateDonateSettingRequest $request)
    {
        $this->settingService->updateDonateSettings($request->validated());

        return redirect()->back()->with('success', 'Donate settings updated successfully.');
    }
}
