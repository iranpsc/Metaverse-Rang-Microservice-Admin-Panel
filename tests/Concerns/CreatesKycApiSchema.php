<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesKycApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpKycApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createKycVerifyTextsTable();
        $this->createKycsTable();
        $this->createNotificationsTable();
    }

    private function createKycVerifyTextsTable(): void
    {
        if (Schema::hasTable('kyc_verify_texts')) {
            return;
        }

        Schema::create('kyc_verify_texts', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->timestamps();
        });
    }

    private function createKycsTable(): void
    {
        if (Schema::hasTable('kycs')) {
            return;
        }

        Schema::create('kycs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('fname')->nullable();
            $table->string('lname')->nullable();
            $table->string('melli_code')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('province')->nullable();
            $table->string('gender')->nullable();
            $table->string('melli_card')->nullable();
            $table->string('video')->nullable();
            $table->integer('status')->default(0);
            $table->json('errors')->nullable();
            $table->unsignedBigInteger('verify_text_id')->nullable();
            $table->timestamps();
        });
    }

    private function createNotificationsTable(): void
    {
        if (Schema::hasTable('notifications')) {
            return;
        }

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
}
