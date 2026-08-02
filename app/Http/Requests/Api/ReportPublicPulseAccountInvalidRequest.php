<?php
namespace App\Http\Requests\Api;
use Illuminate\Foundation\Http\FormRequest;
class ReportPublicPulseAccountInvalidRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['reason'=>['required','string','max:1000'], 'detected_at'=>['nullable','date']]; }
}
