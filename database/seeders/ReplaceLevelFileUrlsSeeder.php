<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ReplaceLevelFileUrlsSeeder extends Seeder
{
    private const OLD_URL = 'https://admin.metarang.com';

    private const NEW_URL = 'https://dev-admin.metarang.com';

    private const FILE_COLUMNS = [
        'levels' => ['background_image'],
        'level_gems' => ['png_file', 'fbx_file'],
        'level_gifts' => ['png_file', 'gif_file', 'fbx_file'],
        'level_general_infos' => ['png_file', 'gif_file', 'fbx_file'],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            foreach (self::FILE_COLUMNS as $table => $columns) {
                $updated = 0;

                DB::table($table)->select(['id', ...$columns])->chunkById(200, function ($rows) use ($table, $columns, &$updated) {
                    foreach ($rows as $row) {
                        $changes = [];

                        foreach ($columns as $column) {
                            $value = $row->$column;
                            $replacement = $this->replaceFileValue($value);

                            if ($replacement !== $value) {
                                $changes[$column] = $replacement;
                            }
                        }

                        if ($changes !== []) {
                            $updated += DB::table($table)->where('id', $row->id)->update($changes);
                        }
                    }
                });

                $this->command?->info("Updated {$updated} row(s) in {$table}.");
            }
        });
    }

    private function replaceFileValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Replace JSON string values individually to preserve metadata, keys,
        // numeric precision, and the original JSON formatting elsewhere.
        json_decode($value);
        if (json_last_error() === JSON_ERROR_NONE) {
            return preg_replace_callback('/"(?:[^"\\\\]|\\\\.)*"(\s*:)?/s', function ($match) {
                if (isset($match[1])) {
                    return $match[0];
                }

                $url = json_decode($match[0], true, 512, JSON_THROW_ON_ERROR);
                $replacement = $this->replaceUrl($url);

                return $url === $replacement ? $match[0] : json_encode($replacement, JSON_THROW_ON_ERROR);
            }, $value);
        }

        return $this->replaceUrl($value);
    }

    private function replaceUrl(string $url): string
    {
        if ($url === self::OLD_URL || str_starts_with($url, self::OLD_URL.'/')
            || str_starts_with($url, self::OLD_URL.'?') || str_starts_with($url, self::OLD_URL.'#')) {
            return self::NEW_URL.substr($url, strlen(self::OLD_URL));
        }

        return $url;
    }
}
