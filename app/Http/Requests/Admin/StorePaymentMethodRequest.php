<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:mpesa,bank,other',
            'account_number' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}
