<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SavePlayerIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'player_id' => ['required', 'string', 'max:255'],
        ];
    }

    public function playerId(): string
    {
        return (string) $this->validated()['player_id'];
    }
}
