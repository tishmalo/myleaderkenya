<?php

namespace App\Http\Requests\Admin;

use App\Models\Poll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PollStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'poll_type' => ['required', Rule::in([Poll::TYPE_WORDS, Poll::TYPE_POLITICAL])],
            'status' => ['required', Rule::in([Poll::STATUS_DRAFT, Poll::STATUS_ACTIVE, Poll::STATUS_CLOSED])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['required', 'date', 'after:now', 'after:starts_at'],
            'reveal_results' => ['nullable', 'boolean'],
            'options' => ['required', 'array', 'min:2', 'max:12'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.label' => ['nullable', 'string', 'max:255'],
            'options.*.candidate_id' => ['nullable', 'integer', 'exists:candidates,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ends_at.after' => 'The closing deadline must be a future date and time.',
            'ends_at.after:starts_at' => 'The deadline must come after the opening date.',
            'options.min' => 'A poll needs at least two options.',
            'options.max' => 'A poll can have at most twelve options.',
        ];
    }

    public function attributes(): array
    {
        return [
            'ends_at' => 'closing deadline',
            'starts_at' => 'opening date',
            'poll_type' => 'poll type',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('reveal_results')) {
            $this->merge(['reveal_results' => $this->boolean('reveal_results') ? 1 : 0]);
        }
    }
}
