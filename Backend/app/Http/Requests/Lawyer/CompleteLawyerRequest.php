<?php

namespace App\Http\Requests\Lawyer;

use Illuminate\Foundation\Http\FormRequest;

class CompleteLawyerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lawyer_comment' => ['required', 'string', 'max:2000'],
        ];
    }
}
