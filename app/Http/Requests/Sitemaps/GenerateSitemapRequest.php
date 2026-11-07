<?php

namespace App\Http\Requests\Sitemaps;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSitemapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['target' => ['nullable', 'string', 'max:191'], 'language' => ['nullable', 'string', 'max:16', 'regex:/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/i']];
    }
}
