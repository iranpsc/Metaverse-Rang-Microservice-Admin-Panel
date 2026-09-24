<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLES = [
        'level_gems',
        'level_gifts',
        'level_general_infos',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            DB::statement("ALTER TABLE {$tableName} MODIFY fbx_file MEDIUMTEXT NULL");
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            DB::statement("ALTER TABLE {$tableName} MODIFY fbx_file TEXT NULL");
        }
    }
};
