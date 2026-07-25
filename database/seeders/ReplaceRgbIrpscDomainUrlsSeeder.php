<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Replaces legacy domain strings across all text-like columns in base tables (MySQL).
 * Updates run in SQL (nested REPLACE + WHERE filter) so rows are never loaded into PHP.
 */
class ReplaceRgbIrpscDomainUrlsSeeder extends Seeder
{
    private const OLD_ADMIN = 'admin.rgb.irpsc.com';

    private const NEW_ADMIN = 'admin.metarang.com';

    private const OLD_API = 'api.rgb.irpsc.com';

    private const NEW_API = 'api.metarang.com';

    /** Narrows scans to rows that may contain either hostname. */
    private const WHERE_LIKE = '%rgb.irpsc.com%';

    public function run(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('ReplaceRgbIrpscDomainUrlsSeeder requires a MySQL connection.');
        }

        $database = $this->getDatabaseName();

        $sql = <<<'SQL'
            SELECT c.TABLE_NAME, c.COLUMN_NAME
            FROM information_schema.COLUMNS c
            INNER JOIN information_schema.TABLES t
                ON c.TABLE_SCHEMA = t.TABLE_SCHEMA
                AND c.TABLE_NAME = t.TABLE_NAME
            WHERE c.TABLE_SCHEMA = ?
                AND t.TABLE_TYPE = 'BASE TABLE'
                AND c.DATA_TYPE IN (
                    'char', 'varchar',
                    'tinytext', 'text', 'mediumtext', 'longtext',
                    'json'
                )
                AND (c.GENERATION_EXPRESSION IS NULL OR c.GENERATION_EXPRESSION = '')
            ORDER BY c.TABLE_NAME, c.COLUMN_NAME
            SQL;

        $columns = DB::select($sql, [$database]);

        foreach ($columns as $row) {
            $this->updateColumn((string) $row->TABLE_NAME, (string) $row->COLUMN_NAME);
        }
    }

    private function getDatabaseName(): string
    {
        $name = DB::connection()->getDatabaseName();
        if ($name === '') {
            throw new RuntimeException('Database name is empty; check DB_DATABASE.');
        }

        return $name;
    }

    private function updateColumn(string $table, string $column): void
    {
        $this->assertSafeIdent($table);
        $this->assertSafeIdent($column);

        $qt = $this->quoteIdent($table);
        $qc = $this->quoteIdent($column);

        $sql = "UPDATE {$qt} SET {$qc} = REPLACE(REPLACE({$qc}, ?, ?), ?, ?) WHERE {$qc} LIKE ?";

        DB::affectingStatement($sql, [
            self::OLD_API,
            self::NEW_API,
            self::OLD_ADMIN,
            self::NEW_ADMIN,
            self::WHERE_LIKE,
        ]);
    }

    private function quoteIdent(string $name): string
    {
        $this->assertSafeIdent($name);

        return DB::connection()->getQueryGrammar()->wrap($name);
    }

    private function assertSafeIdent(string $name): void
    {
        if ($name === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new RuntimeException("Invalid SQL identifier: {$name}");
        }
    }
}
