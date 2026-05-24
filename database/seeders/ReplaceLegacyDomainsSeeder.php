<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplaceLegacyDomainsSeeder extends Seeder
{
    /**
     * URLs to replace.
     */
    private array $replacements = [
        'https://api.rgb.irpsc.com'   => 'https://api.metarang.com',
        'https://admin.rgb.irpsc.com' => 'https://admin.metarang.com',
    ];

    /**
     * Execute the database seeds.
     */
    public function run(): void
    {
        DB::disableQueryLog();

        $database = DB::getDatabaseName();

        // Get all tables
        $tables = DB::select('SHOW TABLES');

        $tableKey = 'Tables_in_' . $database;

        foreach ($tables as $tableObj) {
            $table = $tableObj->$tableKey;

            // Skip migrations table
            if ($table === 'migrations') {
                continue;
            }

            // Get only text-based columns
            $columns = DB::select("
                SELECT COLUMN_NAME, DATA_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME = ?
                  AND DATA_TYPE IN ('varchar', 'text', 'mediumtext', 'longtext', 'tinytext')
            ", [$database, $table]);

            if (empty($columns)) {
                continue;
            }

            foreach ($columns as $columnObj) {
                $column = $columnObj->COLUMN_NAME;

                foreach ($this->replacements as $old => $new) {

                    // Skip table update if old URL does not exist
                    $exists = DB::table($table)
                        ->where($column, 'LIKE', '%' . $old . '%')
                        ->exists();

                    if (! $exists) {
                        continue;
                    }

                    // Single SQL update using REPLACE() for performance
                    DB::statement("
                        UPDATE `{$table}`
                        SET `{$column}` = REPLACE(`{$column}`, ?, ?)
                        WHERE `{$column}` LIKE ?
                    ", [
                        $old,
                        $new,
                        '%' . $old . '%',
                    ]);

                    $this->command?->info(
                        "Updated {$table}.{$column}: {$old} => {$new}"
                    );
                }
            }
        }

        $this->command?->info('URL replacement completed.');
    }
}
