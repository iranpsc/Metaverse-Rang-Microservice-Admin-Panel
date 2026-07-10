<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('message_templates');
    }

    public function down(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->longText('email_content');
            $table->text('sms_content');
            $table->timestamps();
        });
    }
};
