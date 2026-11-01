<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite';

    /**
     * Remove duplicate unique_id rows within the same tab.
     * Keeps the row with a non-null translation when possible, otherwise the lowest id.
     * Adds a unique index so the same unique_id cannot appear twice in one tab.
     */
    public function up(): void
    {
        $connection = DB::connection($this->connection);

        $connection->transaction(function () use ($connection) {
            $duplicateGroups = $connection->table('fields')
                ->select('tab_id', 'unique_id')
                ->groupBy('tab_id', 'unique_id')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicateGroups as $group) {
                $rowsQuery = $connection->table('fields')
                    ->where('tab_id', $group->tab_id);

                if ($group->unique_id === null) {
                    $rowsQuery->whereNull('unique_id');
                } else {
                    $rowsQuery->where('unique_id', $group->unique_id);
                }

                $rows = $rowsQuery
                    ->orderByRaw('CASE WHEN translation IS NULL OR translation = \'\' THEN 1 ELSE 0 END')
                    ->orderBy('id')
                    ->get(['id']);

                $idsToDelete = $rows->skip(1)->pluck('id')->all();

                if ($idsToDelete !== []) {
                    $connection->table('fields')->whereIn('id', $idsToDelete)->delete();
                }
            }
        });

        Schema::connection($this->connection)->table('fields', function (Blueprint $table) {
            $table->unique(['tab_id', 'unique_id'], 'fields_tab_id_unique_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->table('fields', function (Blueprint $table) {
            $table->dropUnique('fields_tab_id_unique_id_unique');
        });
    }
};
