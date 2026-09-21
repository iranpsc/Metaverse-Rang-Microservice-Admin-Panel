<?php

namespace App\Http\Requests\Dynasty;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDynastyPrizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'member' => [
                'required',
                Rule::in(['father', 'mother', 'brother', 'offspring', 'sister', 'husband', 'wife']),
                Rule::unique('dynasty_prizes', 'member'),
            ],
            'satisfaction' => ['required', 'numeric', 'min:0'],
            'introduction_profit_increase' => ['required', 'numeric', 'min:0'],
            'accumulated_capital_reserve' => ['required', 'numeric', 'min:0'],
            'data_storage' => ['required', 'numeric', 'min:0'],
            'psc' => ['required', 'numeric', 'min:0'],
        ];
    }
}
