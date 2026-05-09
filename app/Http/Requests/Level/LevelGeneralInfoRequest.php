<?php

namespace App\Http\Requests\Level;

use Illuminate\Foundation\Http\FormRequest;

class LevelGeneralInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'score' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string', 'max:6000'],
            'rank' => ['required', 'integer', 'min:0'],
            'subcategories' => ['required', 'integer', 'min:0'],
            'persian_font' => ['required', 'string', 'max:255'],
            'english_font' => ['required', 'string', 'max:255'],
            'file_volume' => ['required', 'decimal:0,3', 'min:0'],
            'used_colors' => ['required', 'string', 'max:500'],
            'points' => ['required', 'integer', 'min:0'],
            'designer' => ['required', 'string', 'max:255'],
            'model_designer' => ['required', 'string', 'max:255'],
            'creation_date' => ['required', 'string', 'max:255'],
            'has_animation' => ['required', 'boolean'],
            'lines' => ['required', 'integer', 'min:0'],
            'png_file' => ['nullable', 'image', 'mimes:png', 'max:5120'],
            'fbx_file' => ['nullable', 'file', 'max:302400'],
            'gif_file' => ['nullable', 'file', 'mimes:gif', 'max:5120'],
            'phone_verification' => ['nullable', 'integer', 'digits:6', 'is_valid_verify_code'],
        ];

        if (app()->environment('production')) {
            array_unshift($rules['phone_verification'], 'required');
        }

        return $rules;
    }
}


