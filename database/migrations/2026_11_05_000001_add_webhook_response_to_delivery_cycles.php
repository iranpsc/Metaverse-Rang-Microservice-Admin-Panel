<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_delivery_cycles', function (Blueprint $table) {
            $table->uuid('last_batch_id')->nullable()->after('status');
            $table->unsignedInteger('last_http_status')->nullable()->after('last_batch_id');
            $table->longText('last_response_body')->nullable()->after('last_http_status');
            $table->text('last_error')->nullable()->after('last_response_body');
        });
    }

    public function down(): void
    {
        Schema::table('content_delivery_cycles', function (Blueprint $table) {
            $table->dropColumn(['last_batch_id', 'last_http_status', 'last_response_body', 'last_error']);
        });
    }
};
