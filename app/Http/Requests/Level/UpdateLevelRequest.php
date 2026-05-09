<?php

namespace App\Http\Requests\Level;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLevelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $levelId = $this->route('level');

        if (is_object($levelId) && method_exists($levelId, 'getKey')) {
            $levelId = $levelId->getKey();
        }

        $rules = [
            'name' => ['required', 'string'],
            'slug' => [
                'required',
                'string',
                Rule::unique('levels', 'slug')->ignore($levelId),
            ],
            'score' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,png,bmp,jpeg', 'max:1024'],
            'background_image' => ['nullable', 'image', 'max:1024'],
        ];

        $rules['phone_verification'] = ['nullable', 'integer', 'digits:6', 'is_valid_verify_code'];

        if (app()->environment('production')) {
            array_unshift($rules['phone_verification'], 'required');
        }

        return $rules;
    }
}


