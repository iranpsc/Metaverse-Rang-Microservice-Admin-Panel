<?php

namespace App\Http\Requests\Level;

use Illuminate\Foundation\Http\FormRequest;

class LevelGiftRequest extends FormRequest
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
            'monthly_capacity_count' => ['required', 'integer', 'min:0'],
            'store_capacity' => ['required', 'boolean'],
            'sell_capacity' => ['required', 'boolean'],
            'features' => ['required', 'string', 'max:5000'],
            'sell' => ['required', 'boolean'],
            'vod_document_registration' => ['required', 'boolean'],
            'seller_link' => ['required', 'string', 'max:255'],
            'designer' => ['required', 'string', 'max:255'],
            'three_d_model_volume' => ['required', 'decimal:0,4', 'min:0'],
            'three_d_model_points' => ['required', 'integer', 'min:0'],
            'three_d_model_lines' => ['required', 'integer', 'min:0'],
            'has_animation' => ['required', 'boolean'],
            'png_file' => ['nullable', 'image', 'mimes:png', 'max:20480'],
            'fbx_file' => ['nullable', 'file', 'max:512000'],
            'gif_file' => ['nullable', 'file', 'mimes:gif', 'max:20480'],
            'rent' => ['required', 'boolean'],
            'vod_count' => ['required', 'integer', 'min:0'],
            'start_vod_id' => ['nullable', 'string', 'max:255'],
            'end_vod_id' => ['nullable', 'string', 'max:255'],
            'phone_verification' => ['nullable', 'integer', 'digits:6', 'is_valid_verify_code'],
        ];

        if (app()->environment('production')) {
            array_unshift($rules['phone_verification'], 'required');
        }

        return $rules;
    }
}


