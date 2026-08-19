<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'promo_video' => ['nullable', 'url', function ($attribute, $value, $fail) {
                $host = strtolower((string) parse_url($value, PHP_URL_HOST));
                if ($host && ! in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'], true)) {
                    $fail('The ' . $attribute . ' must be a YouTube link.');
                }
            }],
            'is_active' => 'sometimes|boolean',
        ];
    }
}
