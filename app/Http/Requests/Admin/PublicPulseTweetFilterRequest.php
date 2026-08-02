<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
class PublicPulseTweetFilterRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return ['page'=>['nullable','integer','min:1'], 'page_size'=>['nullable','integer','min:1','max:200'], 'sentiment'=>['nullable','in:positive,neutral,negative'], 'source'=>['nullable','in:x,youtube,reddit,rss']];
    }
}
