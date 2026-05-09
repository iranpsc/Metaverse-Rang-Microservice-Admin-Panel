<?php

namespace App\Http\Requests\Version;

use Illuminate\Foundation\Http\FormRequest;

class StoreVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:20000'],
            'version_title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date_format:Y/m/d'],
        ];
    }
}


