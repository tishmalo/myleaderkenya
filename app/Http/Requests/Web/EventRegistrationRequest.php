<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class EventRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'attendee_names' => ['nullable', 'array', 'max:9'],
            'attendee_names.*' => ['nullable', 'string', 'max:255'],
            'user_type' => ['required', 'string', 'in:Aspirant,Campaign Manager,Voter,Party Representative,Trainers,Ambassador'],
            'position' => ['required', 'string', 'in:President,Governor,Senator,Women Rep,MPs,MCAs,Other'],
        ];
    }
}
