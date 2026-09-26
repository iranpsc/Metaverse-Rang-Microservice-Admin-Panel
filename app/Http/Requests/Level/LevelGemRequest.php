<?php

namespace App\Http\Requests\Level;

use App\Http\Requests\Level\Concerns\ValidatesLevelFbxFile;
use Illuminate\Foundation\Http\FormRequest;

class LevelGemRequest extends FormRequest
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
            'thread' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:0'],
            'volume' => ['required', 'decimal:0,3', 'min:0'],
            'color' => ['required', 'string', 'max:255'],
            'png_file' => ['nullable', 'image', 'mimes:png', 'max:5120'],
            'encryption' => ['required', 'boolean'],
            'designer' => ['required', 'string', 'max:255'],
            'has_animation' => ['required', 'boolean'],
            'lines' => ['required', 'integer', 'min:0'],
        ], $this->fbxFileRules());
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateFbxFileMap($validator);
        });
    }
}
