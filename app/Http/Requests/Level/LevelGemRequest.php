<?php

namespace App\Http\Requests\Level;

use Illuminate\Foundation\Http\FormRequest;

class LevelGemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:6000'],
            'thread' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:0'],
            'volume' => ['required', 'decimal:0,3', 'min:0'],
            'color' => ['required', 'string', 'max:255'],
            'png_file' => ['nullable', 'image', 'mimes:png', 'max:5120'],
            'fbx_file' => ['nullable', 'file', 'max:307200'],
            'encryption' => ['required', 'boolean'],
            'designer' => ['required', 'string', 'max:255'],
            'has_animation' => ['required', 'boolean'],
            'lines' => ['required', 'integer', 'min:0'],
            'phone_verification' => ['nullable', 'integer', 'digits:6', 'is_valid_verify_code'],
        ];

        if (app()->environment('production')) {
            array_unshift($rules['phone_verification'], 'required');
        }

        return $rules;
    }
}


