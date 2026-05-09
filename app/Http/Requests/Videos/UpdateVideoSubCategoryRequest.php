<?php

namespace App\Http\Requests\Videos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'video_category_id' => ['required', 'integer', 'exists:video_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:20000'],
            'image' => ['nullable', 'image'],
            'icon' => ['nullable', 'file', 'mimes:svg', 'max:1024'],
        ];
    }
}


