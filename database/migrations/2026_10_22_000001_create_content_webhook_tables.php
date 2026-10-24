<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_delivery_state', function (Blueprint $t) {
            $t->unsignedInteger('id')->primary();
            $t->unsignedBigInteger('next_patch')->default(1);
            $t->uuid('active_cycle')->nullable();
        });
        DB::table('content_delivery_state')->insert(['id' => 1, 'next_patch' => 1]);
        // Independent projection lock: saving content never waits for HTTP I/O.
        DB::table('content_delivery_state')->insert(['id' => 2, 'next_patch' => 1]);
        Schema::create('content_delivery_cycles', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->unsignedBigInteger('cutoff');
            $t->string('trigger');
            $t->json('actor')->nullable();
            $t->string('status')->default('pending');
            $t->timestamps();
        });
        Schema::create('content_webhook_batches', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('version')->unique();
            $t->longText('payload');
            $t->string('status')->default('pending')->index();
            $t->unsignedInteger('attempts')->default(0);
            $t->unsignedInteger('retry_attempts')->default(0);
            $t->timestamp('available_at')->nullable();
            $t->unsignedInteger('http_status')->nullable();
            $t->text('error')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamps();
        });
        Schema::create('content_changes', function (Blueprint $t) {
            $t->id();
            $t->uuid('source_id')->unique();
            $t->unsignedBigInteger('activity_id')->unique();
            $t->uuid('batch_id')->nullable()->index();
            $t->json('data');
            $t->timestamps();
        });
        Schema::create('content_change_receipts', function (Blueprint $t) {
            $t->uuid('source_id')->primary();
            $t->unsignedBigInteger('activity_id');
        });
        Schema::create('content_webhook_attempts', function (Blueprint $t) {
            $t->id();
            $t->uuid('batch_id')->index();
            $t->unsignedInteger('number');
            $t->unsignedInteger('http_status')->nullable();
            $t->text('error')->nullable();
            $t->timestamps();
        });
        foreach (array_unique([config('database.default'), 'sqlite']) as $connection) {
            Schema::connection($connection)->create('content_change_outbox', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->json('data');
                $t->timestamp('created_at');
                $t->timestamp('projected_at')->nullable()->index();
            });
        }
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->insertOrIgnore([
                'name' => 'send-content-webhooks', 'guard_name' => 'admin',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach (array_unique([config('database.default'), 'sqlite']) as $connection) {
            Schema::connection($connection)->dropIfExists('content_change_outbox');
        }
        foreach (['content_webhook_attempts', 'content_change_receipts', 'content_changes', 'content_webhook_batches', 'content_delivery_cycles', 'content_delivery_state'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
