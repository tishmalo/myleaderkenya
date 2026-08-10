<?php

namespace App\Http\Requests\Admin;

use App\Models\CampaignToolPackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignToolPackageRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->canAccess('campaign-tool-requests.update') ?? false; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'entitlement_quantity' => $this->input('entitlement_type') === 'quantity' ? $this->input('entitlement_quantity') : null,
            'duration_days' => $this->input('entitlement_type') === 'time' ? $this->input('duration_days') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'=>['required','string','max:255'], 'description'=>['nullable','string','max:2000'],
            'token_cost'=>['required','integer','min:1'],
            'entitlement_type'=>['required',Rule::in(CampaignToolPackage::ENTITLEMENT_TYPES)],
            'entitlement_quantity'=>['nullable','integer','min:1','required_if:entitlement_type,quantity'],
            'duration_days'=>['nullable','integer','min:1','required_if:entitlement_type,time'],
            'fulfilment_instructions'=>['nullable','string','max:4000'], 'is_active'=>['nullable','boolean'],
            'sort_order'=>['nullable','integer','min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'duration_days.required_if' => 'Enter the number of days for a time-based package.',
            'entitlement_quantity.required_if' => 'Enter the usage allowance for a quantity-based package.',
        ];
    }
}
