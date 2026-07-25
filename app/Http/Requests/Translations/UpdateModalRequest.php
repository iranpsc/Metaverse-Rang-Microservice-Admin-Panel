<?php

namespace App\Http\Requests\Translations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $modalId = $this->route('modal')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sqlite.modals', 'name')->ignore($modalId),
            ],
        ];
    }
}


