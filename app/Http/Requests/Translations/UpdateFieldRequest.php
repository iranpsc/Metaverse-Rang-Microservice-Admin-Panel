<?php

namespace App\Http\Requests\Translations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'translation' => ['required', 'string', 'max:5000'],
        ];
    }
}


