<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Web\StoreCampaignToolFeatureRequest as WebStoreCampaignToolFeatureRequest;
use Illuminate\Validation\Validator;

class StoreCampaignToolFeatureRequest extends WebStoreCampaignToolFeatureRequest
{
    /**
     * API submissions skip the browser-only honeypot, timing and reCAPTCHA
     * checks, but still require a way to follow up with the requester.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (blank($this->input('email')) && blank($this->input('phone'))) {
                $validator->errors()->add('phone', 'Enter an email or phone so the team can follow up.');
            }
        });
    }
}
