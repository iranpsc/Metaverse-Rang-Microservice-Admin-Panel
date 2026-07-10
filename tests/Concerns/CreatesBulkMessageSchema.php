<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesBulkMessageSchema
{
    protected function setUpBulkMessageSchema(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('activitylog.enabled', false);

        $this->createCoreTables();
        $this->createPermissionTables();
        $this->createBulkMessageTables();
    }

    private function createCoreTables(): void
    {
        if (! Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->boolean('active')->default(1);
                $table->string('image')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('code');
                $table->string('wallet_address')->nullable();
                $table->string('password')->default('secret');
                $table->string('ip')->default('127.0.0.1');
                $table->unsignedBigInteger('score')->default(0);
                $table->timestamp('last_seen')->useCurrent();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('levels')) {
            Schema::create('levels', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug');
                $table->string('score')->default('0');
                $table->string('background_image')->default('');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('level_user')) {
            Schema::create('level_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('level_id');
                $table->timestamps();
            });
        }
    }

    private function createPermissionTables(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type']);
            });
        }
    }

    private function createBulkMessageTables(): void
    {
        if (! Schema::hasTable('bulk_message_logs')) {
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
    }
}
