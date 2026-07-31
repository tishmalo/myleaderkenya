<?php

namespace App\Http\Requests\Web;

use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAspirantToolActivationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(AspirantWorkspaceService $workspaceService): array
    {
        $definitions = $workspaceService->toolDefinitions();

        return [
            'tool_key' => ['required', 'string', Rule::in(array_keys($definitions))],
            'tool_title' => ['required', 'string', 'max:255'],
            'campaign_tool_id' => ['nullable', 'integer', 'exists:campaign_tools,id'],
            'disabled_reason' => ['nullable', 'string', 'max:2000'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
