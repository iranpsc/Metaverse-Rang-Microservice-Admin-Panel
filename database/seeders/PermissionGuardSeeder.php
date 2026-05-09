<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionGuardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::chunk(100, function ($permissions) {
            foreach ($permissions as $permission) {
                $permission->guard_name = 'sanctum';
                $permission->save();
            }
        });

        Role::chunk(100, function ($roles) {
            foreach ($roles as $role) {
                $role->guard_name = 'sanctum';
                $role->save();
            }
        });
    }
}
