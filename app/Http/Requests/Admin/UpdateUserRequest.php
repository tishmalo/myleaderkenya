<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // the user ID is retrieved from the route parameter
        $user = $this->route('user');
        $userId = $user ? $user->id : null;

        return [
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|unique:users,email,' . $userId,
            'phone'        => 'nullable|string|max:20',
            'gender'       => 'nullable|in:male,female,other',
            'year_of_birth'=> 'nullable|integer|min:1900|max:' . date('Y'),
            'county'       => 'nullable|string|max:100',
            'constituency' => 'nullable|string|max:100',
            'ward'         => 'nullable|string|max:100',
            'polling_station' => 'nullable|string|max:255',
            'country_of_residence' => 'nullable|string|max:100',
        ];
    }
}
