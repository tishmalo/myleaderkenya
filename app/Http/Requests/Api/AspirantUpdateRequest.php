<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AspirantUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'nick_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'phone_1' => ['sometimes', 'nullable', 'string', 'max:20'],
            'phone_2' => ['sometimes', 'nullable', 'string', 'max:20'],
            'email' => ['sometimes', 'nullable', 'string', 'lowercase', 'email', 'max:255'],
            'email_1' => ['sometimes', 'nullable', 'string', 'lowercase', 'email', 'max:255'],
            'email_2' => ['sometimes', 'nullable', 'string', 'lowercase', 'email', 'max:255'],
            'position_id' => ['sometimes', 'exists:positions,id'],
            'political_party_id' => ['sometimes', 'nullable', 'exists:political_parties,id'],
            'party' => ['sometimes', 'nullable', 'exists:political_parties,id'],
            'profile_picture' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'profile_pic' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'cover_photo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'campaign_poster' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'campaign_video' => ['sometimes', 'nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:51200'],
            'campaign_skiza_audio' => ['sometimes', 'nullable', 'file', 'mimes:mp3,wav,m4a,aac,ogg', 'max:20480'],
            'about' => ['sometimes', 'nullable', 'string'],
            'county' => ['sometimes', 'nullable', 'string', 'max:255'],
            'constituency' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ward' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
