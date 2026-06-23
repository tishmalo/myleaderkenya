<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Services\Admin\PaymentMethodService;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function __construct(
        private PaymentMethodService $paymentMethodService
    ) {}

    public function store(StorePaymentMethodRequest $request)
    {
        $this->paymentMethodService->createPaymentMethod($request->validated());

        return redirect()->back()->with('success', 'Payment method added successfully.');
    }

    public function update(StorePaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        $this->paymentMethodService->updatePaymentMethod($paymentMethod, $request->validated());

        return redirect()->back()->with('success', 'Payment method updated successfully.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $this->paymentMethodService->deletePaymentMethod($paymentMethod);

        return redirect()->back()->with('success', 'Payment method deleted successfully.');
    }
}
