<?php

namespace App\Repositories;

use App\Models\Level\Level;
use App\Models\Level\LevelGeneralInfo;
use Illuminate\Support\Facades\DB;

class LevelGeneralInfoRepository
{
    public function createForLevel(Level $level, array $attributes): LevelGeneralInfo
    {
        return DB::transaction(fn () => $level->generalInfo()->create($attributes));
    }

    public function update(LevelGeneralInfo $generalInfo, array $attributes): LevelGeneralInfo
    {
        DB::transaction(fn () => $generalInfo->update($attributes));

        return $generalInfo->fresh();
    }

    /**
     * @return array{found: bool, url: ?string}
     */
    public function removeFbxFileEntry(LevelGeneralInfo $generalInfo, string $fileKey): array
    {
        $files = is_array($generalInfo->fbx_file) ? $generalInfo->fbx_file : [];

        if (! array_key_exists($fileKey, $files)) {
            return ['found' => false, 'url' => null];
        }

        $url = is_string($files[$fileKey]) ? $files[$fileKey] : null;
        unset($files[$fileKey]);
        $remainingValue = count($files) > 0 ? $files : null;

        $this->update($generalInfo, ['fbx_file' => $remainingValue]);

        return ['found' => true, 'url' => $url];
    }

    /**
     * @return array{found: bool, url: ?string}
     */
    public function clearFileField(LevelGeneralInfo $generalInfo, string $field): array
    {
        $url = is_string($generalInfo->{$field}) ? $generalInfo->{$field} : null;

        if (! $url) {
            return ['found' => false, 'url' => null];
        }

        $this->update($generalInfo, [$field => null]);

        return ['found' => true, 'url' => $url];
    }
}
