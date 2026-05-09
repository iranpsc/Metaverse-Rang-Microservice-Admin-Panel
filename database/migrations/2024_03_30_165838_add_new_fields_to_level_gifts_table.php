<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('level_gifts', function (Blueprint $table) {
            $table->integer('vod_count')->default(0);
            $table->string('start_vod_id')->nullable();
            $table->string('end_vod_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('level_gifts', function (Blueprint $table) {
            $table->dropColumn('vod_counts');
            $table->dropColumn('start_vod_id');
            $table->dropColumn('end_vod_id');
        });
    }
};
