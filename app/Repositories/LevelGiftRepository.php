<?php

namespace App\Repositories;

use App\Models\Level\Level;
use App\Models\Level\LevelGift;
use Illuminate\Support\Facades\DB;

class LevelGiftRepository
{
    public function createForLevel(Level $level, array $attributes): LevelGift
    {
        return DB::transaction(fn () => $level->gift()->create($attributes));
    }

    public function update(LevelGift $gift, array $attributes): LevelGift
    {
        DB::transaction(fn () => $gift->update($attributes));

        return $gift->fresh();
    }

    /**
     * @return array{found: bool, url: ?string}
     */
    public function removeFbxFileEntry(LevelGift $gift, string $fileKey): array
    {
        $files = is_array($gift->fbx_file) ? $gift->fbx_file : [];

        if (! array_key_exists($fileKey, $files)) {
            return ['found' => false, 'url' => null];
        }

        $url = is_string($files[$fileKey]) ? $files[$fileKey] : null;
        unset($files[$fileKey]);
        $remainingValue = count($files) > 0 ? $files : null;

        $this->update($gift, ['fbx_file' => $remainingValue]);

        return ['found' => true, 'url' => $url];
    }

    /**
     * @return array{found: bool, url: ?string}
     */
    public function clearFileField(LevelGift $gift, string $field): array
    {
        $url = is_string($gift->{$field}) ? $gift->{$field} : null;

        if (! $url) {
            return ['found' => false, 'url' => null];
        }

        $this->update($gift, [$field => null]);

        return ['found' => true, 'url' => $url];
    }
}
