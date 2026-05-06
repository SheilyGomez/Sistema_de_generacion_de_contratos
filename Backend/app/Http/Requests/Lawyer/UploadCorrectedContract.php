<?php

namespace App\Http\Requests\Lawyer;

use Illuminate\Foundation\Http\FormRequest;

class UploadCorrectedContract extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'corrected_contract' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
