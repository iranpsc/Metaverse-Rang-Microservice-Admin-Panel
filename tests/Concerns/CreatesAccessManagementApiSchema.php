<?php

namespace Tests\Concerns;

use App\Models\Admin;
use App\Models\Employee\Employee;
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
        $this->createEmployeesTable();
    }

    private function createEmployeesTable(): void
    {
        if (Schema::hasTable('employees')) {
            return;
        }

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('fname');
            $table->string('lname');
            $table->string('melli_code');
            $table->date('birthdate');
            $table->string('hometown');
            $table->string('father_name');
            $table->enum('gender', ['male', 'female']);
            $table->enum('marriage_status', ['single', 'married']);
            $table->string('home_phone');
            $table->string('phone');
            $table->string('address');
            $table->integer('employee_code');
            $table->date('entry_date');
            $table->string('email');
            $table->timestamps();
        });
    }

    protected function createEmployee(array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'fname' => 'Ali',
            'lname' => 'Karimi',
            'melli_code' => (string) random_int(1000000000, 9999999999),
            'birthdate' => '1990-01-01',
            'hometown' => 'Tehran',
            'father_name' => 'Hassan',
            'gender' => 'male',
            'marriage_status' => 'single',
            'home_phone' => '02112345678',
            'phone' => '0912'.random_int(1000000, 9999999),
            'email' => Str::uuid().'@employee.test',
            'address' => 'Tehran, Iran',
            'employee_code' => random_int(1000, 9999),
            'entry_date' => '2020-01-01',
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
