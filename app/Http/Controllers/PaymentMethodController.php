<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('sort_order')
                            ->orderBy('name')
                            ->get();

        return view('payment-methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        return view('payment-methods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:mpesa,bank,paypal,cash,other',
            'account_number'=> 'nullable|string|max:100',
            'account_name'  => 'nullable|string|max:255',
            'phone_number'  => 'nullable|string|max:20',
            'bank_name'     => 'nullable|string|max:100',
            'branch'        => 'nullable|string|max:100',
            'instructions'  => 'nullable|string',
            'is_active'     => 'boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        PaymentMethod::create($request->all());

        return redirect()->route('payment-methods.index')
                         ->with('success', 'Payment method added successfully.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:mpesa,bank,paypal,cash,other',
            'account_number'=> 'nullable|string|max:100',
            'account_name'  => 'nullable|string|max:255',
            'phone_number'  => 'nullable|string|max:20',
            'bank_name'     => 'nullable|string|max:100',
            'branch'        => 'nullable|string|max:100',
            'instructions'  => 'nullable|string',
            'is_active'     => 'boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $paymentMethod->update($request->all());

        return redirect()->route('payment-methods.index')
                         ->with('success', 'Payment method updated successfully.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return redirect()->route('payment-methods.index')
                         ->with('success', 'Payment method deleted successfully.');
    }
}