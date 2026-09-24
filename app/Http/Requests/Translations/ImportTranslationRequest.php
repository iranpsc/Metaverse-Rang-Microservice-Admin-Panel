<?php

namespace App\Http\Requests\Translations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ImportTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimetypes:application/json,text/plain,text/json,application/octet-stream',
            ],
        ];

        if ($this->route('translation') === null) {
            $rules['code'] = ['required', 'string', 'max:10', 'unique:sqlite.translations,code'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'code.required' => 'A language code is required for import.',
            'code.unique' => 'A translation for this language already exists.',
            'file.required' => 'A translation JSON file is required.',
            'file.file' => 'The uploaded translation must be a valid file.',
            'file.mimes' => 'The translation import file must be a JSON file.',
            'file.max' => 'The translation file may not be greater than 10MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $file = $this->file('file');

            if (! $file || $validator->errors()->has('file')) {
                return;
            }

            $extension = strtolower((string) $file->getClientOriginalExtension());

            if ($extension !== 'json') {
                $validator->errors()->add('file', 'The translation import file must be a JSON file.');
            }
        });
    }
}
