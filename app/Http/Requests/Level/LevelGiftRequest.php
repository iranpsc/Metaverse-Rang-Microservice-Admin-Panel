<?php

namespace App\Http\Requests\Level;

use App\Http\Requests\Level\Concerns\ValidatesLevelFbxFile;
use Illuminate\Foundation\Http\FormRequest;

class LevelGiftRequest extends FormRequest
{
    use ValidatesLevelFbxFile;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('fbx_file') && is_string($this->input('fbx_file'))) {
            $decoded = json_decode($this->input('fbx_file'), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge(['fbx_file' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return array_merge([
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
            'gif_file' => ['nullable', 'file', 'mimes:gif', 'max:20480'],
            'rent' => ['required', 'boolean'],
            'vod_count' => ['required', 'integer', 'min:0'],
            'start_vod_id' => ['nullable', 'string', 'max:255'],
            'end_vod_id' => ['nullable', 'string', 'max:255'],
        ], $this->fbxFileRules());
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateFbxFileMap($validator);
        });
    }
}
