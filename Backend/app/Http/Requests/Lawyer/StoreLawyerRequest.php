<?php

namespace App\Http\Requests\Lawyer;

use Illuminate\Foundation\Http\FormRequest;

class StoreLawyerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contract_generation_id' => ['required', 'exists:contract_generations,id'],
            'lawyer_id' => ['required', 'exists:users,id'],
            'freelancer_comment' => ['required', 'string', 'max:2000'],
        ];
    }
}
