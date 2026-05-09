<?php

namespace App\Http\Requests\Videos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:20000'],
            'image' => ['nullable', 'image', 'max:1024'],
            'video_file_name' => ['nullable', 'string'],
            'creator_code' => ['nullable', 'string', 'exists:users,code'],
        ];
    }
}
