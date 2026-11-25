<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store the last manual dictionary export for each locale on the source connection.
     */
    public function up(): void
    {
        Schema::connection('sqlite')->create('translation_export_snapshots', function (Blueprint $table) {
            $table->foreignId('translation_id')->primary()->constrained()->cascadeOnDelete();
            $table->json('dictionary');
        });
    }

    /**
     * Remove the manual export baselines.
     */
    public function down(): void
    {
        Schema::connection('sqlite')->dropIfExists('translation_export_snapshots');
    }
};
