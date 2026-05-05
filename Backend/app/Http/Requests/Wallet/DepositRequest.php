<?php

namespace App\Http\Requests\Wallet;

use App\Enums\TransactionType;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "card_number" => ["required", "string", 'regex:/^\d{13}$/'],
            "cvv" => ["required", "string", 'regex:/^\d{3}$/'],
            "expiration_date" => [
                "required",
                "string",
                'regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
                function ($attribute, $value, $fail) {
                    [$month, $year] = explode("/", $value);
                    $expiration = Carbon::createFromFormat(
                        "m/y",
                        "$month/$year",
                    )
                        ->endOfMonth()
                        ->startOfDay();
                    if ($expiration->isPast()) {
                        $fail("La tarjeta ha expirado.");
                    }
                },
            ],
            "amount" => ["required", "numeric", "min:1"],
        ];
    }

    public function messages(): array
    {
        return [
            "card_number.regex" =>
                "El número de tarjeta debe tener 13 dígitos.",
            "cvv.regex" => "El CVV debe tener 3 dígitos.",
            "expiration_date.regex" =>
                "La fecha de expiración debe tener el formato MM/YY.",
        ];
    }
}
