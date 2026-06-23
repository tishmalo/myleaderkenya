<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\PaymentMethodService;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function __construct(
        private PaymentMethodService $paymentMethodService
    ) {}

    /**
     * Get active payment methods.
     */
    public function index(): JsonResponse
    {
        $paymentMethods = $this->paymentMethodService->getActivePaymentMethods();

        return response()->json([
            'success' => true,
            'message' => 'Payment methods retrieved successfully',
            'data' => $paymentMethods
        ]);
    }
}
