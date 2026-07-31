<?php

namespace App\Http\Requests\PoliticalParty;

use Illuminate\Foundation\Http\FormRequest;

abstract class PartyCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nick_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'position_id' => ['required', 'exists:positions,id'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'max:5120'],
            'campaign_video_url' => ['nullable', 'url', 'max:255'],
            'campaign_song_url' => ['nullable', 'url', 'max:255'],
            'campaign_skiza_audio' => [
                'nullable',
                'file',
                'mimes:mp3,wav,m4a,aac,ogg',
                'max:20480',
            ],
            'campaign_poster' => ['nullable', 'image', 'max:5120'],
            'about' => ['nullable', 'string'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'x_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'whatsapp_group_url' => ['nullable', 'url', 'max:255'],
            'country' => ['nullable', 'string'],
            'county' => ['nullable', 'string'],
            'constituency' => ['nullable', 'string'],
            'ward' => ['nullable', 'string'],
            'support_contacts' => ['nullable', 'array'],
            'support_contacts.*.support_group_type_id' => [
                'nullable',
                'exists:support_group_types,id',
            ],
            'support_contacts.*.name' => ['nullable', 'string', 'max:255'],
            'support_contacts.*.email' => ['nullable', 'email', 'max:255'],
            'support_contacts.*.phone' => ['nullable', 'string', 'max:50'],
        ];
    }
}
