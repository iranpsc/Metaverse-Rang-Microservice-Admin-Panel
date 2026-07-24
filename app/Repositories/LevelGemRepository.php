<?php

namespace App\Repositories;

use App\Models\Level\Level;
use App\Models\Level\LevelGem;
use Illuminate\Support\Facades\DB;

class LevelGemRepository
{
    public function createForLevel(Level $level, array $attributes): LevelGem
    {
        return DB::transaction(fn () => $level->gem()->create($attributes));
    }

    public function update(LevelGem $gem, array $attributes): LevelGem
    {
        DB::transaction(fn () => $gem->update($attributes));

        return $gem->fresh();
    }

    /**
     * @return array{found: bool, url: ?string}
     */
    public function removeFbxFileEntry(LevelGem $gem, string $fileKey): array
    {
        $files = is_array($gem->fbx_file) ? $gem->fbx_file : [];

        if (! array_key_exists($fileKey, $files)) {
            return ['found' => false, 'url' => null];
        }

        $url = is_string($files[$fileKey]) ? $files[$fileKey] : null;
        unset($files[$fileKey]);
        $remainingValue = count($files) > 0 ? $files : null;

        $this->update($gem, ['fbx_file' => $remainingValue]);

        return ['found' => true, 'url' => $url];
    }

    /**
     * @return array{found: bool, url: ?string}
     */
    public function clearFileField(LevelGem $gem, string $field): array
    {
        $url = is_string($gem->{$field}) ? $gem->{$field} : null;

        if (! $url) {
            return ['found' => false, 'url' => null];
        }

        $this->update($gem, [$field => null]);

        return ['found' => true, 'url' => $url];
    }
}
