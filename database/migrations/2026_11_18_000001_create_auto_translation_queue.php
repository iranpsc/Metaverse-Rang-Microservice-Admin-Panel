<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the run and queue tables on the same SQLite connection.
     *
     * @return void Changes the SQLite schema in place.
     */
    public function up(): void
    {
        $schema = Schema::connection('sqlite');
        if (! $schema->hasTable('auto_translation_runs')) {
            $schema->create('auto_translation_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('translation_id')->constrained()->cascadeOnDelete();
                $table->boolean('active')->nullable();
                $table->unique(['translation_id', 'active']);
                $table->string('status');
                $table->json('actor');
                $table->unsignedInteger('step')->default(0);
                $table->unsignedInteger('total')->default(0);
                $table->unsignedInteger('processed')->default(0);
                $table->unsignedInteger('translated')->default(0);
                $table->json('items')->nullable();
                $table->json('results')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('auto_translation_jobs')) {
            $schema->create('auto_translation_jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }
    }

    /**
     * Remove the dedicated queue table and its run history.
     *
     * @return void Rolls back the SQLite schema changes.
     */
    public function down(): void
    {
        $schema = Schema::connection('sqlite');
        $schema->dropIfExists('auto_translation_jobs');
        $schema->dropIfExists('auto_translation_runs');
    }
};
