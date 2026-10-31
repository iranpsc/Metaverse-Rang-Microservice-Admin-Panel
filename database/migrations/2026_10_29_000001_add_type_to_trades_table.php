<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('trades') && ! Schema::hasColumn('trades', 'type')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->string('type')->default('land')->after('date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('trades') && Schema::hasColumn('trades', 'type')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
