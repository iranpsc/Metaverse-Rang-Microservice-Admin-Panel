<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_message_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('bulk_send_id');
            $table->string('channel', 10);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('message_id')->nullable();
            $table->string('status', 20);
            $table->text('error')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_message_logs');
    }
};
