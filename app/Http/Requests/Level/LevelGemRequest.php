<?php

namespace App\Http\Requests\Level;

use App\Http\Controllers\FileUploadController;
use Illuminate\Foundation\Http\FormRequest;

class LevelGemRequest extends FormRequest
{
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:6000'],
            'thread' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:0'],
            'volume' => ['required', 'decimal:0,3', 'min:0'],
            'color' => ['required', 'string', 'max:255'],
            'png_file' => ['nullable', 'image', 'mimes:png', 'max:5120'],
            'fbx_file' => ['nullable', 'array', 'max:20'],
            'fbx_file.*' => ['required', 'string', 'url', 'max:2048'],
            'encryption' => ['required', 'boolean'],
            'designer' => ['required', 'string', 'max:255'],
            'has_animation' => ['required', 'boolean'],
            'lines' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fbxFile = $this->input('fbx_file');
            if (! is_array($fbxFile)) {
                return;
            }

            $allowed = FileUploadController::ALLOWED_EXTENSIONS;

            foreach ($fbxFile as $fileType => $url) {
                $normalizedType = strtolower((string) preg_replace('/_\d+$/', '', (string) $fileType));
                if (! in_array($normalizedType, $allowed, true)) {
                    $validator->errors()->add(
                        'fbx_file',
                        'کلیدهای فایل مدل باید یکی از این فرمت‌ها باشند: ' . implode(', ', $allowed)
                    );
                    return;
                }

                $urlExtension = $this->extensionFromUrl(is_string($url) ? $url : '');
                if ($urlExtension === '' || ! in_array($urlExtension, $allowed, true)) {
                    $validator->errors()->add(
                        'fbx_file',
                        'پسوند لینک فایل مدل باید یکی از این فرمت‌ها باشد: ' . implode(', ', $allowed)
                    );
                    return;
                }

                if ($normalizedType !== $urlExtension && ! $this->isCompatibleImageType($normalizedType, $urlExtension)) {
                    $validator->errors()->add(
                        'fbx_file',
                        "نوع فایل «{$normalizedType}» با پسوند لینک «{$urlExtension}» هم‌خوانی ندارد."
                    );
                    return;
                }
            }
        });
    }

    private function extensionFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        return strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
    }

    private function isCompatibleImageType(string $keyType, string $urlExtension): bool
    {
        $jpegFamily = ['jpeg', 'jpg'];

        return in_array($keyType, $jpegFamily, true) && in_array($urlExtension, $jpegFamily, true);
    }
}
