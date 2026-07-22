<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username'     => 'required|string|max:255|unique:users,username',
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|unique:users,email',
            'phone'        => 'nullable|string|max:20',
            'gender'       => 'nullable|in:male,female,other',
            'year_of_birth'=> 'nullable|integer|min:1900|max:' . date('Y'),
            'county'       => 'nullable|string|max:100',
            'constituency' => 'nullable|string|max:100',
            'ward'         => 'nullable|string|max:100',
            'polling_station' => 'nullable|string|max:255',
            'country_of_residence' => 'nullable|string|max:100',
            'password'     => 'required|string|min:6|confirmed',
            'role_id'      => ['nullable', 'integer', Rule::exists('roles', 'id')->whereIn('name', [Role::USER, Role::ADMIN])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $roleId = $this->input('role_id');

            if (! $roleId) {
                return;
            }

            $role = Role::query()->find($roleId);

            if (! $role) {
                return;
            }

            if ($role->name === Role::ADMIN && ! $this->user()?->isSuperAdmin()) {
                $validator->errors()->add('role_id', 'Only a super admin can create another admin.');
            }
        });
    }
}
