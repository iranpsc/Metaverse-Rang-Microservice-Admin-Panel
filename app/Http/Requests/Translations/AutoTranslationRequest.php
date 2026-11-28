<?php

namespace App\Http\Requests\Translations;

use Illuminate\Foundation\Http\FormRequest;

class AutoTranslationRequest extends FormRequest
{
    /**
     * Allow translation managers to choose a configured translation provider.
     *
     * @return bool Whether the authenticated admin may start a run.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Apply OpenAI as the backwards-compatible API default when no provider is sent.
     *
     * @return void Normalizes the request before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['provider' => $this->input('provider', 'openai')]);
    }

    /**
     * Validate the selected provider identifier.
     *
     * @return array<string, array<int, string>> Request validation rules.
     */
    public function rules(): array
    {
        return ['provider' => ['required', 'string', 'in:openai,cloudflare']];
    }
}
