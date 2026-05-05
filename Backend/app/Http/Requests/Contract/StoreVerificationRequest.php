<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class StoreVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contract' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'contract.required' => 'Debes subir un archivo PDF.',
            'contract.mimes' => 'El archivo debe ser un PDF.',
            'contract.max' => 'El archivo no debe superar los 10 MB.',
        ];
    }
}
