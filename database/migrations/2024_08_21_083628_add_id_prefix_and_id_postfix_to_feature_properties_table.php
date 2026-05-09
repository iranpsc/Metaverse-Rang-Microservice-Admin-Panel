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
        Schema::table('feature_properties', function (Blueprint $table) {
            $table->string('id_prefix')->nullable()->after('id');
            $table->unsignedBigInteger('id_postfix')->nullable()->after('id_prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_properties', function (Blueprint $table) {
            $table->dropColumn('id_prefix');
            $table->dropColumn('id_postfix');
        });
    }
};
