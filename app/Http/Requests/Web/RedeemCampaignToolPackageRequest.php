<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RedeemCampaignToolPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $campaignToolRequest = $this->route('campaignToolRequest');

        return [
            'package_id' => [
                'required',
                'integer',
                Rule::exists('campaign_tool_packages', 'id')
                    ->where('is_active', true)
                    ->where('campaign_tool_id', $campaignToolRequest?->campaign_tool_id),
            ],
        ];
    }

    public function messages(): array
    {
        return ['package_id.exists' => 'Select an active package belonging to this campaign tool.'];
    }
}
