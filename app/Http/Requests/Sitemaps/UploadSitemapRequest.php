<?php

namespace App\Http\Requests\Sitemaps;

use Illuminate\Foundation\Http\FormRequest;

class UploadSitemapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['language' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/i'], 'file' => ['required', 'file', 'mimes:xml', 'max:20480']];
    }
}
