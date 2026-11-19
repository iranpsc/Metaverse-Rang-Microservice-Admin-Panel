<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the latest provider error to persisted run status.
     *
     * @return void Updates the translation SQLite schema.
     */
    public function up(): void
    {
        Schema::connection('sqlite')->table('auto_translation_runs', function (Blueprint $table) {
            $table->text('error')->nullable();
        });
    }

    /**
     * Remove the persisted provider error.
     *
     * @return void Rolls back the error column on the translation SQLite connection.
     */
    public function down(): void
    {
        Schema::connection('sqlite')->table('auto_translation_runs', function (Blueprint $table) {
            $table->dropColumn('error');
        });
    }
};
