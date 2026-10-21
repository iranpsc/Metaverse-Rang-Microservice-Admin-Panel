<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReplaceLevelFileUrlsSeeder extends Seeder
{
    private const OLD_URL = 'https://admin.metarang.com';

    private const FILE_COLUMNS = [
        'levels' => ['background_image'],
        'level_gems' => ['png_file', 'fbx_file'],
        'level_gifts' => ['png_file', 'gif_file', 'fbx_file'],
        'level_general_infos' => ['png_file', 'gif_file', 'fbx_file'],
    ];

    public function run(): void
    {
        $appUrl = rtrim(trim((string) config('app.url')), '/');

        if (! filter_var($appUrl, FILTER_VALIDATE_URL)
            || ! in_array(parse_url($appUrl, PHP_URL_SCHEME), ['http', 'https'], true)
            || parse_url($appUrl, PHP_URL_QUERY) !== null
            || parse_url($appUrl, PHP_URL_FRAGMENT) !== null) {
            throw new InvalidArgumentException('APP_URL must be a full HTTP(S) URL without a query or fragment.');
        }

        if ($appUrl === self::OLD_URL) {
            return;
        }

        DB::transaction(function () use ($appUrl) {
            foreach (self::FILE_COLUMNS as $table => $columns) {
                $updated = 0;

                DB::table($table)->select(['id', ...$columns])->chunkById(200, function ($rows) use ($table, $columns, $appUrl, &$updated) {
                    foreach ($rows as $row) {
                        $changes = [];

                        foreach ($columns as $column) {
                            $value = $row->$column;
                            $replacement = $this->replaceFileValue($value, $appUrl);

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

    private function replaceFileValue(?string $value, string $appUrl): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Replace JSON string values individually to preserve metadata, keys,
        // numeric precision, and the original JSON formatting elsewhere.
        json_decode($value);
        if (json_last_error() === JSON_ERROR_NONE) {
            return preg_replace_callback('/"(?:[^"\\\\]|\\\\.)*"(\s*:)?/s', function ($match) use ($appUrl) {
                if (isset($match[1])) {
                    return $match[0];
                }

                $url = json_decode($match[0], true, 512, JSON_THROW_ON_ERROR);
                $replacement = $this->replaceUrl($url, $appUrl);

                return $url === $replacement ? $match[0] : json_encode($replacement, JSON_THROW_ON_ERROR);
            }, $value);
        }

        return $this->replaceUrl($value, $appUrl);
    }

    private function replaceUrl(string $url, string $appUrl): string
    {
        if ($url === self::OLD_URL || str_starts_with($url, self::OLD_URL.'/')
            || str_starts_with($url, self::OLD_URL.'?') || str_starts_with($url, self::OLD_URL.'#')) {
            return $appUrl.substr($url, strlen(self::OLD_URL));
        }

        return $url;
    }
}
