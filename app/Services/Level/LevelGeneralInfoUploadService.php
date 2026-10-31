<?php

namespace App\Services\Level;

use App\Http\Requests\Level\LevelGeneralInfoRequest;
use App\Models\Level\LevelGeneralInfo;
use App\Services\Level\Concerns\HandlesLevelFbxFiles;
use Illuminate\Support\Facades\Storage;

class LevelGeneralInfoUploadService
{
    use HandlesLevelFbxFiles;

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
                    'url' => url('uploads/'.$path),
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
                $replacedFiles = array_merge($replacedFiles, $this->extractStoragePaths($generalInfo->{$field}));
            }
        }

        return array_values(array_unique($replacedFiles));
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

    /** @return list<string> */
    private function extractStoragePaths(mixed $value): array
    {
        if (is_array($value)) {
            $paths = [];
            foreach ($value as $entry) {
                $paths = array_merge($paths, $this->extractStoragePaths($entry));
            }

            return array_values(array_unique($paths));
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        if (filter_var($value, FILTER_VALIDATE_URL) && ! str_starts_with($value, rtrim(url('uploads'), '/'))) {
            return [];
        }

        $path = $this->extractStoragePath($value);

        return $path ? [$path] : [];
    }
}
