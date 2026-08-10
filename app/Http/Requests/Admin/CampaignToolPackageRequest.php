<?php

namespace App\Http\Requests\Admin;

use App\Models\CampaignToolPackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignToolPackageRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->canAccess('campaign-tool-requests.update') ?? false; }
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
}
