<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentMethodStoreRequest;
use App\Http\Requests\Admin\PaymentMethodUpdateRequest;
use App\Models\PaymentMethod;
use App\Services\Admin\PaymentMethodService;

class PaymentMethodController extends Controller
{
    public function __construct(
        private PaymentMethodService $paymentMethodService
    ) {}

    public function index()
    {
        $paymentMethods = $this->paymentMethodService->getAllPaymentMethods();

        return view('payment-methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        return view('payment-methods.create');
    }

    public function store(PaymentMethodStoreRequest $request)
    {
        $this->paymentMethodService->createPaymentMethod($request->validated());

        return redirect()->route('payment-methods.index')
                         ->with('success', 'Payment method added successfully.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('payment-methods.edit', compact('paymentMethod'));
    }

    public function update(PaymentMethodUpdateRequest $request, PaymentMethod $paymentMethod)
    {
        $this->paymentMethodService->updatePaymentMethod($paymentMethod, $request->validated());

        return redirect()->route('payment-methods.index')
                         ->with('success', 'Payment method updated successfully.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $this->paymentMethodService->deletePaymentMethod($paymentMethod);

        return redirect()->route('payment-methods.index')
                         ->with('success', 'Payment method deleted successfully.');
    }
}
