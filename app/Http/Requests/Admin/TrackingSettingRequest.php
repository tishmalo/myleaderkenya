<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TrackingSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Each ID may be pasted with or without its vendor prefix; the service
     * normalises it before saving.
     */
    public function rules(): array
    {
        return [
            'tracking_public' => ['nullable', 'boolean'],
            'tracking_admin' => ['nullable', 'boolean'],
            'google_analytics_id' => ['nullable', 'string', 'max:40', 'regex:/^((G|UA|AW|DC)-)?[A-Za-z0-9-]{4,40}$/'],
            'google_tag_manager_id' => ['nullable', 'string', 'max:40', 'regex:/^(GTM-)?[A-Za-z0-9-]{4,30}$/'],
            'google_adsense_client' => ['nullable', 'string', 'max:40', 'regex:/^(ca-pub-)?[0-9]{10,20}$/'],
            'google_adsense_auto_ads' => ['nullable', 'boolean'],
            'facebook_pixel_id' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]{5,20}$/'],
            'head_scripts' => ['nullable', 'string', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'google_analytics_id.regex' => 'That is not a Google Analytics ID. Copy the measurement ID, e.g. G-ABC123XYZ4.',
            'google_tag_manager_id.regex' => 'That is not a Google Tag Manager ID, e.g. GTM-ABC1234.',
            'google_adsense_client.regex' => 'That is not an AdSense publisher ID. Copy the ca-pub number, e.g. ca-pub-1234567890123456.',
            'facebook_pixel_id.regex' => 'That is not a Meta pixel ID. Use the digits only, e.g. 123456789012345.',
            'head_scripts.max' => 'The custom head scripts are too long (20000 characters maximum).',
        ];
    }

    public function attributes(): array
    {
        return [
            'head_scripts' => 'custom head scripts',
        ];
    }
}
