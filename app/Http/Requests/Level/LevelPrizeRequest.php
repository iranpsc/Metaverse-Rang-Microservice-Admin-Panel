<?php

namespace App\Http\Requests\Level;

use Illuminate\Foundation\Http\FormRequest;

class LevelPrizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'psc' => ['required', 'integer', 'min:0'],
            'yellow' => ['required', 'integer', 'min:0'],
            'blue' => ['required', 'integer', 'min:0'],
            'red' => ['required', 'integer', 'min:0'],
            'effect' => ['required', 'integer', 'min:0'],
            'satisfaction' => ['required', 'decimal:0,4', 'min:0'],
            'phone_verification' => ['nullable', 'integer', 'digits:6', 'is_valid_verify_code'],
        ];

        if (app()->environment('production')) {
            array_unshift($rules['phone_verification'], 'required');
        }

        return $rules;
    }
}


