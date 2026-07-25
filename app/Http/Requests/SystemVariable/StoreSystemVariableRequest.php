<?php

namespace App\Http\Requests\SystemVariable;

use Illuminate\Foundation\Http\FormRequest;

class StoreSystemVariableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:system_variables,slug'],
            'value' => ['required', 'numeric'],
            'note' => ['nullable', 'string'],
        ];
    }
}


