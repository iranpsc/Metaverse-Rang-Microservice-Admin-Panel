<?php

namespace App\Http\Requests\Translations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tabId = $this->route('tab')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sqlite.tabs', 'name')->ignore($tabId),
            ],
        ];
    }
}


