<?php

namespace App\Http\Requests\Level;

use App\Http\Controllers\FileUploadController;
use Illuminate\Foundation\Http\FormRequest;

class LevelGiftRequest extends FormRequest
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
            'monthly_capacity_count' => ['required', 'integer', 'min:0'],
            'store_capacity' => ['required', 'boolean'],
            'sell_capacity' => ['required', 'boolean'],
            'features' => ['required', 'string', 'max:5000'],
            'sell' => ['required', 'boolean'],
            'vod_document_registration' => ['required', 'boolean'],
            'seller_link' => ['required', 'string', 'max:255'],
            'designer' => ['required', 'string', 'max:255'],
            'three_d_model_volume' => ['required', 'decimal:0,4', 'min:0'],
            'three_d_model_points' => ['required', 'integer', 'min:0'],
            'three_d_model_lines' => ['required', 'integer', 'min:0'],
            'has_animation' => ['required', 'boolean'],
            'png_file' => ['nullable', 'image', 'mimes:png', 'max:20480'],
            'fbx_file' => ['nullable', 'array', 'max:20'],
            'fbx_file.*' => ['required', 'string', 'url', 'max:2048'],
            'gif_file' => ['nullable', 'file', 'mimes:gif', 'max:20480'],
            'rent' => ['required', 'boolean'],
            'vod_count' => ['required', 'integer', 'min:0'],
            'start_vod_id' => ['nullable', 'string', 'max:255'],
            'end_vod_id' => ['nullable', 'string', 'max:255'],
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
