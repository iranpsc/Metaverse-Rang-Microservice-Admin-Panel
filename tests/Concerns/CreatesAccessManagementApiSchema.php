<?php

namespace Tests\Concerns;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait CreatesAccessManagementApiSchema
{
    use CreatesAuthApiSchema;

    protected function setUpAccessManagementApiSchema(): void
    {
        $this->setUpAuthApiSchema();
        $this->createUsersTable();
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
            $table->string('code')->nullable();
            $table->string('wallet_address')->nullable();
            $table->string('password')->default('secret');
            $table->string('ip')->default('127.0.0.1');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function createUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Ali Karimi',
            'email' => Str::uuid().'@user.test',
            'phone' => '0912'.random_int(1000000, 9999999),
            'code' => (string) random_int(1000, 9999),
            'password' => 'secret',
            'ip' => '127.0.0.1',
        ], $attributes));
    }

    protected function createRole(array $attributes = []): Role
    {
        $name = $attributes['name'] ?? 'role-'.Str::uuid();

        return Role::create(array_merge([
            'name' => $name,
            'title' => $attributes['title'] ?? 'Role Title',
            'guard_name' => 'sanctum',
        ], $attributes));
    }

    protected function createPermission(array $attributes = []): Permission
    {
        $name = $attributes['name'] ?? 'permission-'.Str::uuid();

        return Permission::create(array_merge([
            'name' => $name,
            'title' => $attributes['title'] ?? 'Permission Title',
            'guard_name' => 'sanctum',
        ], $attributes));
    }

    protected function createAdminAssignableRole(array $attributes = []): Role
    {
        return $this->createRole(array_merge(['guard_name' => 'admin'], $attributes));
    }

    protected function createAdminDirectPermission(array $attributes = []): Permission
    {
        return $this->createPermission(array_merge(['guard_name' => 'web'], $attributes));
    }

    protected function createRegularAdminRecord(array $attributes = []): Admin
    {
        return Admin::withoutEvents(function () use ($attributes) {
            return Admin::create(array_merge([
                'name' => 'Regular Admin',
                'email' => Str::uuid().'@admin.test',
                'password' => bcrypt('password'),
                'phone' => '0912'.random_int(1000000, 9999999),
                'active' => 1,
            ], $attributes));
        });
    }

    protected function createSuperAdminRecord(array $attributes = []): Admin
    {
        $admin = Admin::withoutEvents(function () use ($attributes) {
            return Admin::create(array_merge([
                'name' => 'Super Admin Record',
                'email' => Str::uuid().'@super.test',
                'password' => bcrypt('password'),
                'phone' => '0912'.random_int(1000000, 9999999),
                'active' => 1,
            ], $attributes));
        });

        $role = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'admin'],
            ['title' => 'Super Admin']
        );

        $admin->assignRole($role);

        return $admin;
    }
}
