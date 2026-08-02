<?php

namespace App\Http\Requests\PoliticalParty;

use Illuminate\Foundation\Http\FormRequest;

class StorePartyOfficialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'political_party_id' => [
                'sometimes',
                'required',
                'exists:political_parties,id',
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:party_admin,party_staff'],
        ];
    }
}
