<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLES = ['level_gems', 'level_gifts'];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            DB::statement("ALTER TABLE {$tableName} MODIFY fbx_file TEXT NULL");
            $this->convertExistingUrlsToJson($tableName);
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            $this->convertJsonBackToSingleUrl($tableName);
            DB::statement("ALTER TABLE {$tableName} MODIFY fbx_file VARCHAR(191) NULL");
        }
    }

    private function convertExistingUrlsToJson(string $tableName): void
    {
        DB::table($tableName)
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($tableName) {
                foreach ($rows as $row) {
                    $value = $row->fbx_file;

                    if ($value === null || $value === '') {
                        continue;
                    }

                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        continue;
                    }

                    $path = parse_url($value, PHP_URL_PATH) ?: $value;
                    $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
                    if ($extension === '') {
                        $extension = 'fbx';
                    }

                    DB::table($tableName)
                        ->where('id', $row->id)
                        ->update([
                            'fbx_file' => json_encode(
                                [$extension => $value],
                                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                            ),
                        ]);
                }
            });
    }

    private function convertJsonBackToSingleUrl(string $tableName): void
    {
        DB::table($tableName)
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($tableName) {
                foreach ($rows as $row) {
                    $value = $row->fbx_file;

                    if ($value === null || $value === '') {
                        DB::table($tableName)->where('id', $row->id)->update(['fbx_file' => null]);
                        continue;
                    }

                    $decoded = json_decode($value, true);
                    if (! is_array($decoded) || $decoded === []) {
                        continue;
                    }

                    $firstUrl = (string) reset($decoded);

                    DB::table($tableName)
                        ->where('id', $row->id)
                        ->update(['fbx_file' => mb_substr($firstUrl, 0, 191)]);
                }
            });
    }
};
