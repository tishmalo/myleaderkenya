<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCampaignToolFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requester_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'requested_feature' => ['required', 'string', 'max:255'],
            'use_case' => ['nullable', 'string', 'max:2000'],
            'feature_request_tool_id' => ['nullable', 'integer'],
            'other_campaign_tool_ids' => ['nullable', 'array'],
            'other_campaign_tool_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('campaign_tools', 'id')->where('status', 'published'),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (blank($this->input('email')) && blank($this->input('phone'))) {
                $validator->errors()->add('phone', 'Enter an email or phone so the team can follow up.');
            }
        });
    }
}
