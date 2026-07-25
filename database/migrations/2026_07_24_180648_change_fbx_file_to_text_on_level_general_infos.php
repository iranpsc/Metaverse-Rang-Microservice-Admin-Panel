<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLE = 'level_general_infos';

    public function up(): void
    {
        DB::statement('ALTER TABLE '.self::TABLE.' MODIFY fbx_file TEXT NULL');
        $this->convertExistingUrlsToJson();
    }

    public function down(): void
    {
        $this->convertJsonBackToSingleUrl();
        DB::statement('ALTER TABLE '.self::TABLE.' MODIFY fbx_file VARCHAR(191) NULL');
    }

    private function convertExistingUrlsToJson(): void
    {
        DB::table(self::TABLE)
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
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

                    DB::table(self::TABLE)
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

    private function convertJsonBackToSingleUrl(): void
    {
        DB::table(self::TABLE)
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $value = $row->fbx_file;

                    if ($value === null || $value === '') {
                        DB::table(self::TABLE)->where('id', $row->id)->update(['fbx_file' => null]);
                        continue;
                    }

                    $decoded = json_decode($value, true);
                    if (! is_array($decoded) || $decoded === []) {
                        continue;
                    }

                    $firstUrl = (string) reset($decoded);

                    DB::table(self::TABLE)
                        ->where('id', $row->id)
                        ->update(['fbx_file' => mb_substr($firstUrl, 0, 191)]);
                }
            });
    }
};
