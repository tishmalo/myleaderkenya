<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Get all active payment methods for the mobile app
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Payment methods retrieved successfully',
            'data'    => $paymentMethods->map(function ($method) {
                return [
                    'id'              => $method->id,
                    'name'            => $method->name,
                    'type'            => $method->type,
                    'account_number'  => $method->account_number,
                    'account_name'    => $method->account_name,
                    'phone_number'    => $method->phone_number,
                    'bank_name'       => $method->bank_name,
                    'branch'          => $method->branch,
                    'instructions'    => $method->instructions,
                    'is_active'       => $method->is_active,
                ];
            })
        ]);
    }

    /**
     * Optional: Get single payment method (if needed)
     */
    public function show(PaymentMethod $paymentMethod)
    {
        if (!$paymentMethod->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not available'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $paymentMethod
        ]);
    }
}