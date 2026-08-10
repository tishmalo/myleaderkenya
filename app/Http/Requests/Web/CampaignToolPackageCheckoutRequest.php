<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class CampaignToolPackageCheckoutRequest extends FormRequest
{
    protected $dontFlash=['email','phone'];
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['email'=>['required','email','max:255'],'phone'=>['required','string','max:30','regex:/^[0-9+() .-]+$/']]; }
}
