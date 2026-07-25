<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ActivityLogPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'view-activity-logs', 'guard_name' => 'sanctum'],
            ['title' => 'مشاهده گزارش فعالیت‌ها']
        );

        if ($permission->guard_name !== 'sanctum') {
            $permission->guard_name = 'sanctum';
            $permission->save();
        }

        $superAdmin = Role::where('name', 'super-admin')->where('guard_name', 'sanctum')->first();
        if ($superAdmin && ! $superAdmin->hasPermissionTo('view-activity-logs', 'sanctum')) {
            $superAdmin->givePermissionTo($permission);
        }
    }
}
