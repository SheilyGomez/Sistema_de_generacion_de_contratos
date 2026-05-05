<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class StoreGenerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_type' => ['required', 'string', 'max:255'],
            'duration' => ['required', 'string', 'max:255'],
            'payment_amount' => ['required', 'numeric', 'min:0'],
            'payment_currency' => ['required', 'string', 'size:3'],
            'client_name' => ['required', 'string', 'max:255'],
            'freelancer_name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'extra_details' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
