<?php

namespace Tests\Concerns;

use App\Models\Admin;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

trait ActsAsSuperAdmin
{
    protected function actingAsSuperAdmin(): Admin
    {
        $admin = Admin::withoutEvents(function () {
            return Admin::create([
                'name' => 'Super Admin',
                'email' => Str::uuid().'@example.com',
                'password' => bcrypt('password'),
                'phone' => '09120000000',
                'active' => 1,
            ]);
        });

        $role = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'admin'],
            ['title' => 'Super Admin']
        );

        $admin->assignRole($role);

        Sanctum::actingAs($admin, abilities: ['*'], guard: 'admin');

        return $admin;
    }

    protected function actingAsRegularAdmin(): Admin
    {
        $admin = Admin::withoutEvents(function () {
            return Admin::create([
                'name' => 'Regular Admin',
                'email' => Str::uuid().'@example.com',
                'password' => bcrypt('password'),
                'phone' => '09121111111',
                'active' => 1,
            ]);
        });

        Sanctum::actingAs($admin, abilities: ['*'], guard: 'admin');

        return $admin;
    }
}
