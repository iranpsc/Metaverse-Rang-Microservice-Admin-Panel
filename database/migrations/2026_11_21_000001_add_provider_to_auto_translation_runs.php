<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store the provider selected for each translation run.
     *
     * @return void Updates the translation SQLite schema.
     */
    public function up(): void
    {
        Schema::connection('sqlite')->table('auto_translation_runs', function (Blueprint $table) {
            $table->string('provider')->default('openai')->after('translation_id');
        });
    }

    /**
     * Remove the provider selection from persisted runs.
     *
     * @return void Rolls back the translation SQLite schema change.
     */
    public function down(): void
    {
        Schema::connection('sqlite')->table('auto_translation_runs', function (Blueprint $table) {
            $table->dropColumn('provider');
        });
    }
};
