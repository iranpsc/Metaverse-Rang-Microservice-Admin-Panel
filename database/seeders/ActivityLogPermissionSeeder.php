<?php

namespace Database\Seeders;

/**
 * @deprecated Use AdminRoutePermissionsSeeder instead.
 */
class ActivityLogPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminRoutePermissionsSeeder::class);
    }
}
