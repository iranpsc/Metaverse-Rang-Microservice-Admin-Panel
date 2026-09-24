<?php

namespace App\Services\Level\Concerns;

use App\Http\Controllers\FileUploadController;
use InvalidArgumentException;

trait HandlesLevelFbxFiles
{
    /**
     * @param  array<string, mixed>  $fbxFile
     * @return array<string, array{type: string, size: string, url: string}>
     */
    public function validateFbxFileExtensions(array $fbxFile): array
    {
        if (count($fbxFile) > 20) {
            throw new InvalidArgumentException('حداکثر ۲۰ فایل مدل می‌توانید ذخیره کنید.');
        }

        $allowed = FileUploadController::ALLOWED_EXTENSIONS;
        $normalized = [];

        foreach ($fbxFile as $fileType => $entry) {
            $normalizedEntry = $this->normalizeFbxFileEntry($entry, (string) $fileType);

            if ($normalizedEntry === null) {
                throw new InvalidArgumentException('لینک یکی از فایل‌های مدل نامعتبر است.');
            }

            $normalizedType = strtolower((string) preg_replace('/_\d+$/', '', (string) $fileType));
            if (! in_array($normalizedType, $allowed, true)) {
                throw new InvalidArgumentException(
                    'فرمت فایل مدل مجاز نیست. فرمت‌های مجاز: '.implode(', ', $allowed)
                );
            }

            $entryType = strtolower($normalizedEntry['type']);
            if (! in_array($entryType, $allowed, true)) {
                throw new InvalidArgumentException(
                    'فرمت فایل مدل مجاز نیست. فرمت‌های مجاز: '.implode(', ', $allowed)
                );
            }

            $path = parse_url($normalizedEntry['url'], PHP_URL_PATH) ?: $normalizedEntry['url'];
            $urlExtension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

            if ($urlExtension === '' || ! in_array($urlExtension, $allowed, true)) {
                throw new InvalidArgumentException(
                    'پسوند لینک فایل مدل مجاز نیست. فرمت‌های مجاز: '.implode(', ', $allowed)
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

            if ($entryType !== $urlExtension && ! (
                in_array($entryType, $jpegFamily, true) && in_array($urlExtension, $jpegFamily, true)
            )) {
                throw new InvalidArgumentException(
                    "نوع فایل «{$entryType}» با پسوند لینک «{$urlExtension}» هم‌خوانی ندارد."
                );
            }

            $normalized[$fileType] = $normalizedEntry;
        }

        return $normalized;
    }

    /**
     * Merge newly uploaded model-file entries into the existing map without
     * dropping previously stored entries. Conflicting keys get a unique suffix.
     *
     * @param  array<string, mixed>  $existing
     * @param  array<string, array{type: string, size: string, url: string}>  $incoming
     * @return array<string, array{type: string, size: string, url: string}>
     */
    public function mergeFbxFileLinks(array $existing, array $incoming): array
    {
        $merged = [];

        foreach ($existing as $key => $entry) {
            $normalized = $this->normalizeFbxFileEntry($entry, (string) $key);
            if ($normalized !== null) {
                $merged[(string) $key] = $normalized;
            }
        }

        foreach ($incoming as $key => $entry) {
            $normalized = $this->normalizeFbxFileEntry($entry, (string) $key);
            if ($normalized === null) {
                continue;
            }

            if ($this->fbxFileMapContainsUrl($merged, $normalized['url'])) {
                continue;
            }

            $finalKey = $this->uniqueFbxFileKey($merged, (string) $key);
            $merged[$finalKey] = $normalized;
        }

        if (count($merged) > 20) {
            throw new InvalidArgumentException(
                'حداکثر ۲۰ فایل مدل می‌توانید ذخیره کنید. ابتدا برخی فایل‌های قبلی را حذف کنید.'
            );
        }

        return $merged;
    }

    /**
     * @return array{type: string, size: string, url: string}|null
     */
    public function normalizeFbxFileEntry(mixed $entry, string $key = 'file'): ?array
    {
        if (is_string($entry)) {
            $url = trim($entry);
            if ($url === '') {
                return null;
            }

            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            $type = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
            if ($type === '') {
                $type = strtolower((string) preg_replace('/_\d+$/', '', $key)) ?: 'file';
            }

            return [
                'type' => $type,
                'size' => '0',
                'url' => $url,
            ];
        }

        if (! is_array($entry)) {
            return null;
        }

        $url = isset($entry['url']) && is_string($entry['url']) ? trim($entry['url']) : '';
        if ($url === '') {
            return null;
        }

        $type = isset($entry['type']) && is_string($entry['type'])
            ? strtolower(trim($entry['type']))
            : '';

        if ($type === '') {
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            $type = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        }

        if ($type === '') {
            $type = strtolower((string) preg_replace('/_\d+$/', '', $key)) ?: 'file';
        }

        $size = $entry['size'] ?? '0';
        if (is_int($size) || is_float($size)) {
            $size = (string) (int) $size;
        } elseif (! is_string($size)) {
            $size = '0';
        } else {
            $size = trim($size);
            if ($size === '') {
                $size = '0';
            }
        }

        return [
            'type' => $type,
            'size' => $size,
            'url' => $url,
        ];
    }

    public function extractFbxFileUrl(mixed $entry): ?string
    {
        $normalized = $this->normalizeFbxFileEntry($entry);

        return $normalized['url'] ?? null;
    }

    /**
     * @param  array<string, array{type: string, size: string, url: string}>  $map
     */
    private function fbxFileMapContainsUrl(array $map, string $url): bool
    {
        foreach ($map as $entry) {
            if (($entry['url'] ?? null) === $url) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $existing
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
