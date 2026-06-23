<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\Repositories\Admin\SettingRepositoryInterface;

class DonateController extends Controller
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private \App\Services\Admin\PaymentMethodService $paymentMethodService
    ) {}

    public function index()
    {
        $donateWhyText = $this->settingRepository->getByKey('donation_why_text') 
            ?? 'We are non partisan. We purely depend on donations to help us remain neutral. Donations are used to fund the hosting, development, security and the setup of county, constituency and ward youth employment of the secretariate network to help mobilize people to verify and register to vote. Once you donate, you join our "Donor Club" where you receive updates.';
            
        $whatsappLink = $this->settingRepository->getByKey('donation_whatsapp_link') 
            ?? 'https://chat.whatsapp.com/example';

        $paymentMethods = $this->paymentMethodService->getActivePaymentMethods();

        return view('donate', compact('donateWhyText', 'whatsappLink', 'paymentMethods'));
    }
}
