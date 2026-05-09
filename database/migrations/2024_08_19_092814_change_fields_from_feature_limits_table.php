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
        Schema::table('feature_limits', function (Blueprint $table) {
            $table->boolean('price_limit')->default(false)->after('dynasty_owner_limit');
            $table->boolean('individual_buy_limit')->default(false)->change();
            $table->integer('individual_buy_count')->default(0)->after('individual_buy_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_limits', function (Blueprint $table) {
            $table->dropColumn('price_limit');
            $table->integer('individual_buy_limit')->default(0)->change();
            $table->dropColumn('individual_buy_count');
        });
    }
};
