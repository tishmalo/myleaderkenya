<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
        ];
    }
}
