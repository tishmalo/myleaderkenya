<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAspirantMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_video_url' => ['nullable', 'url', 'max:255', 'regex:/^https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//i'],
            'campaign_song_url' => ['nullable', 'url', 'max:255', 'regex:/^https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//i'],
            'campaign_skiza_audio' => ['nullable', 'file', 'mimes:mp3,wav,m4a,aac,ogg', 'max:20480'],
            'campaign_poster' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}