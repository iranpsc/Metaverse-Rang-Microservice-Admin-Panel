<?php

namespace App\Http\Requests\Level;

use Illuminate\Foundation\Http\FormRequest;

class StoreLevelRequest extends FormRequest
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
        $rules = [
            'name' => ['required', 'string', 'unique:levels,name'],
            'slug' => ['required', 'string', 'unique:levels,slug'],
            'score' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,png,bmp,jpeg'],
            'background_image' => ['required', 'image', 'max:5024'],
        ];

        return $rules;
    }
}


