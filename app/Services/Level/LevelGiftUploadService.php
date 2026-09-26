<?php

namespace App\Services\Level;

use App\Http\Requests\Level\LevelGiftRequest;
use App\Models\Level\LevelGift;
use App\Services\Level\Concerns\HandlesLevelFbxFiles;
use Illuminate\Support\Facades\Storage;

class LevelGiftUploadService
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
            'features',
            'seller_link',
            'designer',
            'start_vod_id',
            'end_vod_id',
        ];

        foreach ($stringFields as $field) {
            if (array_key_exists($field, $payload) && $payload[$field] !== null) {
                $payload[$field] = trim((string) $payload[$field]);
            }
        }

        $booleanFields = [
            'store_capacity',
            'sell_capacity',
            'sell',
            'vod_document_registration',
            'has_animation',
            'rent',
        ];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = filter_var($payload[$field], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        $integerFields = [
            'monthly_capacity_count',
            'three_d_model_points',
            'three_d_model_lines',
            'vod_count',
        ];

        foreach ($integerFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = (int) $payload[$field];
            }
        }

        if (array_key_exists('three_d_model_volume', $payload)) {
            $payload['three_d_model_volume'] = (float) $payload['three_d_model_volume'];
        }

        return $payload;
    }

    /**
     * @return array<string, array{path: string, url: string}>
     */
    public function handleFileUploads(LevelGiftRequest $request): array
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
    public function collectReplacedFilePaths(LevelGift $gift, array $fileUploads): array
    {
        $replacedFiles = [];

        foreach ($fileUploads as $field => $fileData) {
            if ($gift->{$field}) {
                $path = $this->extractStoragePath($gift->{$field});
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
