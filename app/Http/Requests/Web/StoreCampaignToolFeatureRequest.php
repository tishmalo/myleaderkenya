<?php

namespace App\Http\Requests\Web;

use App\Services\Web\RecaptchaService;
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
            'g-recaptcha-response' => ['nullable', 'string'],
            'company_website' => ['nullable', 'string'],
            '_load_time' => ['nullable', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (blank($this->input('email')) && blank($this->input('phone'))) {
                $validator->errors()->add('phone', 'Enter an email or phone so the team can follow up.');
            }

            $this->rejectHoneypot($validator);
            $this->rejectFastSubmission($validator);
            $this->rejectInvalidRecaptcha($validator);
        });
    }

    private function rejectHoneypot(Validator $validator): void
    {
        if (! blank($this->input('company_website'))) {
            $validator->errors()->add('company_website', 'Submission rejected.');
        }
    }

    private function rejectFastSubmission(Validator $validator): void
    {
        $loadTime = (int) $this->input('_load_time');
        $submittedAt = now()->getPreciseTimestamp(3);

        if ($loadTime > 0 && ($submittedAt - $loadTime) < 3000) {
            $validator->errors()->add('_load_time', 'Submission too fast. Please try again.');
        }
    }

    private function rejectInvalidRecaptcha(Validator $validator): void
    {
        $recaptcha = app(RecaptchaService::class);

        if ($recaptcha->enabled() && ! $recaptcha->verify($this->input('g-recaptcha-response'))) {
            $validator->errors()->add('g-recaptcha-response', 'Security check failed. Please try again.');
        }
    }
}
