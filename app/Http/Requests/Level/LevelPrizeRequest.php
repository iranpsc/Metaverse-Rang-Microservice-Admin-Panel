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
        ];

        return $rules;
    }
}


