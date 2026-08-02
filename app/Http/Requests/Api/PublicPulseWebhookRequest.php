<?php
namespace App\Http\Requests\Api;
use Illuminate\Foundation\Http\FormRequest;
class PublicPulseWebhookRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'job_id'=>['nullable','string','max:64'], 'job_ref'=>['required','uuid'],
            'status'=>['required','in:queued_pending_capacity,queued,running,degraded,completed,failed'],
            'partial'=>['nullable','boolean'], 'candidate'=>['nullable','string','max:255'],
            'summary'=>['nullable','array'], 'error_msg'=>['nullable','string','max:5000'],
        ];
    }
}
