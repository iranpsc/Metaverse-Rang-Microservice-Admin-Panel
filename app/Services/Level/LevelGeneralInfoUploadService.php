<?php

namespace App\Services\Level;

use App\Http\Controllers\FileUploadController;
use App\Http\Requests\Level\LevelGeneralInfoRequest;
use App\Models\Level\LevelGeneralInfo;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class LevelGeneralInfoUploadService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function preparePayload(array $data): array
    {
        $payload = $data;

        $stringFields = [
            'description',
            'used_colors',
            'persian_font',
            'english_font',
            'designer',
            'model_designer',
            'creation_date',
        ];

        foreach ($stringFields as $field) {
            if (array_key_exists($field, $payload) && $payload[$field] !== null) {
                $payload[$field] = trim((string) $payload[$field]);
            }
        }

        $integerFields = [
            'score',
            'rank',
            'subcategories',
            'points',
            'lines',
        ];

        foreach ($integerFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = (int) $payload[$field];
            }
        }

        $booleanFields = [
            'has_animation',
        ];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = filter_var($payload[$field], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        if (array_key_exists('file_volume', $payload)) {
            $payload['file_volume'] = (float) $payload['file_volume'];
        }

        return $payload;
    }

    /**
     * @return array<string, array{path: string, url: string}>
     */
    public function handleFileUploads(LevelGeneralInfoRequest $request): array
    {
        $uploads = [];

        $fileFields = [
            'png_file',
            'gif_file',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store('levels', 'public');

                $uploads[$field] = [
                    'path' => $path,
                    'url' => url('uploads/' . $path),
                ];
            }
        }

        return $uploads;
    }

    /**
     * @param  array<string, array{path: string, url: string}>  $fileUploads
     * @return list<string>
     */
    public function collectReplacedFilePaths(LevelGeneralInfo $generalInfo, array $fileUploads): array
    {
        $replacedFiles = [];

        foreach ($fileUploads as $field => $fileData) {
            if ($generalInfo->{$field}) {
                $path = $this->extractStoragePath($generalInfo->{$field});
                if ($path) {
                    $replacedFiles[] = $path;
                }
            }
        }

        return $replacedFiles;
    }

    /**
     * @param  array<string, array{path: string, url: string}>  $fileUploads
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function applyUploadedFiles(array $fileUploads, array $validated): array
    {
        foreach ($fileUploads as $field => $fileData) {
            $validated[$field] = $fileData['url'];
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $fbxFile
     * @return array<string, string>
     */
    public function validateFbxFileExtensions(array $fbxFile): array
    {
        if (count($fbxFile) > 20) {
            throw new InvalidArgumentException('حداکثر ۲۰ فایل مدل می‌توانید ذخیره کنید.');
        }

        $allowed = FileUploadController::ALLOWED_EXTENSIONS;
        $normalized = [];

        foreach ($fbxFile as $fileType => $url) {
            if (! is_string($url) || trim($url) === '') {
                throw new InvalidArgumentException('لینک یکی از فایل‌های مدل نامعتبر است.');
            }

            $normalizedType = strtolower((string) preg_replace('/_\d+$/', '', (string) $fileType));
            if (! in_array($normalizedType, $allowed, true)) {
                throw new InvalidArgumentException(
                    'فرمت فایل مدل مجاز نیست. فرمت‌های مجاز: ' . implode(', ', $allowed)
                );
            }

            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            $urlExtension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

            if ($urlExtension === '' || ! in_array($urlExtension, $allowed, true)) {
                throw new InvalidArgumentException(
                    'پسوند لینک فایل مدل مجاز نیست. فرمت‌های مجاز: ' . implode(', ', $allowed)
                );
            }

            $jpegFamily = ['jpeg', 'jpg'];
            $compatibleJpeg = in_array($normalizedType, $jpegFamily, true)
                && in_array($urlExtension, $jpegFamily, true);

            if ($normalizedType !== $urlExtension && ! $compatibleJpeg) {
                throw new InvalidArgumentException(
                    "نوع فایل «{$normalizedType}» با پسوند لینک «{$urlExtension}» هم‌خوانی ندارد."
                );
            }

            $normalized[$fileType] = trim($url);
        }

        return $normalized;
    }

    /**
     * Merge newly uploaded model-file links into the existing map without
     * dropping previously stored entries. Conflicting keys get a unique suffix.
     *
     * @param  array<string, mixed>  $existing
     * @param  array<string, string>  $incoming
     * @return array<string, string>
     */
    public function mergeFbxFileLinks(array $existing, array $incoming): array
    {
        $merged = [];

        foreach ($existing as $key => $url) {
            if (is_string($url) && trim($url) !== '') {
                $merged[(string) $key] = trim($url);
            }
        }

        foreach ($incoming as $key => $url) {
            if (! is_string($url) || trim($url) === '') {
                continue;
            }

            if (in_array(trim($url), $merged, true)) {
                continue;
            }

            $finalKey = $this->uniqueFbxFileKey($merged, (string) $key);
            $merged[$finalKey] = trim($url);
        }

        if (count($merged) > 20) {
            throw new InvalidArgumentException(
                'حداکثر ۲۰ فایل مدل می‌توانید ذخیره کنید. ابتدا برخی فایل‌های قبلی را حذف کنید.'
            );
        }

        return $merged;
    }

    /**
     * @param  list<string|null>  $paths
     */
    public function cleanupFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function extractStoragePath(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $baseUrl = rtrim(url('uploads'), '/');

        if ($baseUrl && str_starts_with($url, $baseUrl)) {
            return ltrim(substr($url, strlen($baseUrl)), '/');
        }

        return ltrim($url, '/');
    }

    /**
     * @param  array<string, string>  $existing
     */
    private function uniqueFbxFileKey(array $existing, string $desiredKey): string
    {
        if (! array_key_exists($desiredKey, $existing)) {
            return $desiredKey;
        }

        $base = strtolower((string) preg_replace('/_\d+$/', '', $desiredKey));
        if ($base === '') {
            $base = 'file';
        }

        $index = 2;
        while (array_key_exists("{$base}_{$index}", $existing)) {
            $index++;
        }

        return "{$base}_{$index}";
    }
}
