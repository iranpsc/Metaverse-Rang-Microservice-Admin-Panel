<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesDashboardSchema
{
    protected function setUpDashboardSchema(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('activitylog.enabled', false);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createAdminsTable();
        $this->createPersonalAccessTokensTable();
        $this->createPermissionTables();
        $this->createUsersTable();
        $this->createKycsTable();
        $this->createFeaturesTable();
        $this->createOrdersTable();
        $this->createPaymentsTable();
        $this->createVariablesTable();
        $this->createReferralsTable();
        $this->createReferralOrderHistoriesTable();
        $this->createDynastiesTable();
    }

    private function createAdminsTable(): void
    {
        if (Schema::hasTable('admins')) {
            return;
        }

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->boolean('active')->default(1);
            $table->string('image')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    private function createPersonalAccessTokensTable(): void
    {
        if (Schema::hasTable('personal_access_tokens')) {
            return;
        }

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    private function createPermissionTables(): void
    {
        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->string('title')->nullable();
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->string('title')->nullable();
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
                $table->primary(
                    ['permission_id', 'model_id', 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            });
        }

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
                $table->primary(
                    ['role_id', 'model_id', 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            });
        }

        if (! Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
            });
        }
    }

    private function createUsersTable(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('code');
            $table->string('wallet_address')->nullable();
            $table->string('password')->default('secret');
            $table->string('ip')->default('127.0.0.1');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->rememberToken();
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
            $table->integer('status')->default(0);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('national_code')->nullable();
            $table->json('errors')->nullable();
            $table->unsignedBigInteger('verify_text_id')->nullable();
            $table->timestamps();
        });
    }

    private function createFeaturesTable(): void
    {
        if (Schema::hasTable('features')) {
            return;
        }

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('map_id')->nullable()->default(0);
            $table->string('type')->nullable()->default('land');
            $table->unsignedBigInteger('owner_id')->default(1);
            $table->timestamps();
        });
    }

    private function createOrdersTable(): void
    {
        if (Schema::hasTable('orders')) {
            return;
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('asset');
            $table->decimal('amount', 16, 4)->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    private function createPaymentsTable(): void
    {
        if (Schema::hasTable('payments')) {
            return;
        }

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ref_id')->nullable();
            $table->string('card_pan')->nullable();
            $table->string('gate_way')->nullable();
            $table->string('product')->nullable();
            $table->decimal('amount', 16, 4)->default(0);
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    private function createVariablesTable(): void
    {
        if (Schema::hasTable('variables')) {
            return;
        }

        Schema::create('variables', function (Blueprint $table) {
            $table->id();
            $table->string('asset');
            $table->integer('price')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    private function createReferralsTable(): void
    {
        if (Schema::hasTable('referrals')) {
            return;
        }

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('referrer_id')->nullable();
            $table->timestamps();
        });
    }

    private function createReferralOrderHistoriesTable(): void
    {
        if (Schema::hasTable('referral_order_histories')) {
            return;
        }

        Schema::create('referral_order_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('referral_id');
            $table->decimal('amount', 16, 4)->default(0);
            $table->timestamps();
        });
    }

    private function createDynastiesTable(): void
    {
        if (Schema::hasTable('dynasties')) {
            return;
        }

        Schema::create('dynasties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }
}
