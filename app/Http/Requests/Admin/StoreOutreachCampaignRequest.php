<?php

namespace App\Http\Requests\Admin;

use App\Models\OutreachCampaign;
use App\Support\OutreachAudience;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOutreachCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::in([OutreachCampaign::CHANNEL_EMAIL, OutreachCampaign::CHANNEL_SMS])],
            'audience' => ['required', Rule::in(OutreachAudience::keys())],
            'subject' => ['nullable', 'string', 'max:150', Rule::requiredIf(fn (): bool => $this->input('channel') === OutreachCampaign::CHANNEL_EMAIL)],
            'body' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'audience.in' => 'Choose who should receive this message.',
            'subject.required' => 'Give the email a subject line.',
            'body.required' => 'Write the message first.',
            'body.max' => 'Keep the message under 2000 characters so it stays a sensible email or SMS.',
        ];
    }

    public function attributes(): array
    {
        return [
            'audience' => 'audience',
            'subject' => 'subject',
            'body' => 'message',
        ];
    }
}
