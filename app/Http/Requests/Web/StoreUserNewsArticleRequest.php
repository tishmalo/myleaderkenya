<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserNewsArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string', 'max:50000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['integer', 'distinct', 'exists:tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'featured_image.max' => 'The featured image may not be larger than 5 MB.',
            'tags.max' => 'You may select up to 10 tags.',
        ];
    }
}
