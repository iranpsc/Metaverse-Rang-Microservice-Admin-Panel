<?php

namespace App\Http\Requests\Translations;

use Illuminate\Foundation\Http\FormRequest;

class StoreModalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'alpha_dash:ascii', 'max:255', 'unique:sqlite.modals,name'],
        ];
    }
}


