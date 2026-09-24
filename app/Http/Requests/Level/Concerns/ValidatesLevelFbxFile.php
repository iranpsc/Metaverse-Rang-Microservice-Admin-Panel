<?php

namespace App\Http\Requests\Level\Concerns;

use App\Http\Controllers\FileUploadController;

trait ValidatesLevelFbxFile
{
    /**
     * @return array<string, list<string>>
     */
    protected function fbxFileRules(): array
    {
        return [
            'fbx_file' => ['nullable', 'array', 'max:20'],
            'fbx_file.*' => ['required', 'array'],
            'fbx_file.*.type' => ['required', 'string', 'max:16'],
            'fbx_file.*.size' => ['required'],
            'fbx_file.*.url' => ['required', 'string', 'url', 'max:2048'],
        ];
    }

    protected function validateFbxFileMap($validator): void
    {
        $fbxFile = $this->input('fbx_file');
        if (! is_array($fbxFile)) {
            return;
        }

        $allowed = FileUploadController::ALLOWED_EXTENSIONS;

        foreach ($fbxFile as $fileType => $entry) {
            $normalizedType = strtolower((string) preg_replace('/_\d+$/', '', (string) $fileType));
            if (! in_array($normalizedType, $allowed, true)) {
                $validator->errors()->add(
                    'fbx_file',
                    'کلیدهای فایل مدل باید یکی از این فرمت‌ها باشند: '.implode(', ', $allowed)
                );

                return;
            }

            if (! is_array($entry)) {
                $validator->errors()->add(
                    'fbx_file.'.$fileType,
                    'ساختار فایل مدل نامعتبر است.'
                );

                return;
            }

            $url = isset($entry['url']) && is_string($entry['url']) ? $entry['url'] : '';
            $entryType = isset($entry['type']) && is_string($entry['type'])
                ? strtolower(trim($entry['type']))
                : '';

            if ($entryType !== '' && ! in_array($entryType, $allowed, true)) {
                $validator->errors()->add(
                    'fbx_file',
                    'فرمت فایل مدل مجاز نیست. فرمت‌های مجاز: '.implode(', ', $allowed)
                );

                return;
            }

            $urlExtension = $this->extensionFromFbxUrl($url);
            if ($urlExtension === '' || ! in_array($urlExtension, $allowed, true)) {
                $validator->errors()->add(
                    'fbx_file',
                    'پسوند لینک فایل مدل باید یکی از این فرمت‌ها باشد: '.implode(', ', $allowed)
                );

                return;
            }

            if ($normalizedType !== $urlExtension && ! $this->isCompatibleFbxImageType($normalizedType, $urlExtension)) {
                $validator->errors()->add(
                    'fbx_file',
                    "نوع فایل «{$normalizedType}» با پسوند لینک «{$urlExtension}» هم‌خوانی ندارد."
                );

                return;
            }

            if ($entryType !== '' && $entryType !== $urlExtension && ! $this->isCompatibleFbxImageType($entryType, $urlExtension)) {
                $validator->errors()->add(
                    'fbx_file',
                    "نوع فایل «{$entryType}» با پسوند لینک «{$urlExtension}» هم‌خوانی ندارد."
                );

                return;
            }
        }
    }

    private function extensionFromFbxUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        return strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
    }

    private function isCompatibleFbxImageType(string $keyType, string $urlExtension): bool
    {
        $jpegFamily = ['jpeg', 'jpg'];

        return in_array($keyType, $jpegFamily, true) && in_array($urlExtension, $jpegFamily, true);
    }
}
