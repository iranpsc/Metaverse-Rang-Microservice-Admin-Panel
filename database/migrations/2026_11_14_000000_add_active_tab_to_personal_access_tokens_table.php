<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->string('active_tab_id', 64)->nullable()->index();
            $table->timestamp('active_tab_last_seen_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->dropIndex(['active_tab_id']);
            $table->dropColumn(['active_tab_id', 'active_tab_last_seen_at']);
        });
    }
};
