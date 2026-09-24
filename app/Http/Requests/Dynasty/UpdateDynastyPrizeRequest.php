<?php

namespace App\Http\Requests\Dynasty;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDynastyPrizeRequest extends FormRequest
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
            'satisfaction' => ['required', 'numeric', 'min:0'],
            'introduction_profit_increase' => ['required', 'numeric', 'min:0'],
            'accumulated_capital_reserve' => ['required', 'numeric', 'min:0'],
            'data_storage' => ['required', 'numeric', 'min:0'],
            'psc' => ['required', 'numeric', 'min:0'],
        ];
    }
}
