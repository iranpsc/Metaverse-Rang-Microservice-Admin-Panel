<?php

namespace App\Http\Requests\Level;

use App\Http\Requests\Level\Concerns\ValidatesLevelFbxFile;
use Illuminate\Foundation\Http\FormRequest;

class LevelGeneralInfoRequest extends FormRequest
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
            'gif_file' => ['nullable', 'file', 'mimes:gif', 'max:5120'],
        ], $this->fbxFileRules());
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateFbxFileMap($validator);
        });
    }
}
