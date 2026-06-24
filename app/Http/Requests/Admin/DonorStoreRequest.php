<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DonorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'payment_method'  => 'required|in:mpesa,bank_transfer,paypal,cash,other',
            'amount'          => 'required|numeric|min:0.01',
            'currency'        => 'nullable|string|max:10',
            'details'         => 'nullable|string',
            'status'          => 'required|in:pending,completed,failed,refunded',
            'payment_details' => 'nullable|array',
        ];
    }
}
