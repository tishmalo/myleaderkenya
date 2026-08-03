<?php
namespace App\Http\Requests\Audit;
use Illuminate\Foundation\Http\FormRequest;
class AuditFilterRequest extends FormRequest {
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['event'=>['nullable','string','max:100'],'module'=>['nullable','string','max:100'],'status'=>['nullable','in:success,failure,pending,partial'],'actor_id'=>['nullable','integer'],'candidate_id'=>['nullable','integer'],'date_from'=>['nullable','date'],'date_to'=>['nullable','date','after_or_equal:date_from']]; }
}
