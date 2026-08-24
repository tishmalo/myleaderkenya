<?php

namespace App\Http\Requests\Web;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAspirantEventRequest extends FormRequest
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
            'description' => 'required|string|max:5000',
            'date' => 'required|date|after:now',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:99999999',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'promo_video' => ['nullable', 'url', 'max:255', function ($attribute, $value, $fail) {
                $host = strtolower((string) parse_url($value, PHP_URL_HOST));
                if ($host && ! in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'], true)) {
                    $fail('The ' . $attribute . ' must be a YouTube link.');
                }
            }],
        ];
    }
}
