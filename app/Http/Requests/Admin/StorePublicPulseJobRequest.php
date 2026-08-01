<?php
namespace App\Http\Requests\Admin;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
class StorePublicPulseJobRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'candidate_id' => ['required','integer','exists:candidates,id'],
            'keywords' => ['nullable','array','max:20'],
            'keywords.*' => ['string','max:100','distinct'],
            'date_from' => ['required','date'],
            'date_to' => ['required','date','after_or_equal:date_from'],
            'limit' => ['required','integer','min:1','max:1000'],
        ];
    }
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->date('date_from') && $this->date('date_to') && Carbon::parse($this->date_from)->diffInDays(Carbon::parse($this->date_to)) > 90) {
                $validator->errors()->add('date_to', 'The date range may not exceed 90 days.');
            }
        }];
    }
}
