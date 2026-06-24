<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CandidateUpdateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'nick_name'       => 'nullable|string|max:100',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'position_id'     => 'required|exists:positions,id',
            'bloc_id'         => 'nullable|exists:blocs,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'about'           => 'nullable|string',
            'county'          => 'nullable|string',
            'constituency'    => 'nullable|string',
            'ward'            => 'nullable|string',
        ];
    }
}
