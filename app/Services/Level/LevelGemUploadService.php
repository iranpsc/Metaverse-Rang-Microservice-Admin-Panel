<?php

namespace App\Services\Level;

use App\Http\Requests\Level\LevelGemRequest;
use App\Models\Level\LevelGem;
use App\Services\Level\Concerns\HandlesLevelFbxFiles;
use Illuminate\Support\Facades\Storage;

class LevelGemUploadService
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
            'name',
            'description',
            'thread',
            'color',
            'designer',
        ];

        foreach ($stringFields as $field) {
            if (array_key_exists($field, $payload) && $payload[$field] !== null) {
                $payload[$field] = trim((string) $payload[$field]);
            }
        }

        $integerFields = [
            'points',
            'lines',
        ];

        foreach ($integerFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = (int) $payload[$field];
            }
        }

        $booleanFields = [
            'encryption',
            'has_animation',
        ];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = filter_var($payload[$field], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        if (array_key_exists('volume', $payload)) {
            $payload['volume'] = (float) $payload['volume'];
        }

        return $payload;
    }

    /**
     * @return array<string, array{path: string, url: string}>
     */
    public function handleFileUploads(LevelGemRequest $request): array
    {
        $uploads = [];

        $fileFields = ['png_file'];

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
    public function collectReplacedFilePaths(LevelGem $gem, array $fileUploads): array
    {
        $replacedFiles = [];

        foreach ($fileUploads as $field => $fileData) {
            if ($gem->{$field}) {
                $path = $this->extractStoragePath($gem->{$field});
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
}
