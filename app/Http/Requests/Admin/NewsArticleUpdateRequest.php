<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NewsArticleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'           => 'required|string|max:255',
            'excerpt'         => 'nullable|string',
            'content'         => 'required|string',
            'featured_image'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video_url'       => 'nullable|url',
            'tags'            => 'nullable|array',
            'tags.*'          => 'exists:tags,id',
            'candidates'      => 'nullable|array',
            'candidates.*'    => 'exists:candidates,id',
            'political_parties' => 'nullable|array',
            'political_parties.*' => 'exists:political_parties,id',
            'sentiment'       => 'required|in:neutral,positive,negative',
            'status'          => 'required|in:draft,published',
        ];
    }
}

