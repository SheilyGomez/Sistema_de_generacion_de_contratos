<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'regex:/^\d+$/', 'max:30'],
            'account_holder' => ['required', 'string', 'max:255'],
            'amount' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($value > $this->user()->balance) {
                        $fail('Fondos insuficientes.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.regex' => 'El número de cuenta solo debe contener dígitos.',
        ];
    }
}
