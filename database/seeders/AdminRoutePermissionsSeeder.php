<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminRoutePermissionsSeeder extends Seeder
{
    private const GUARD = 'sanctum';

    /**
     * All admin panel permissions keyed by slug with Persian titles.
     *
     * @return array<string, string>
     */
    private function permissions(): array
    {
        return [
            'view-dashboard' => 'مشاهده داشبورد',
            'manage-profile' => 'مدیریت پروفایل',
            'view-activity-logs' => 'مشاهده گزارش فعالیت‌ها',
            'view-registration-info' => 'مشاهده اطلاعات ثبت نام',
            'verify-kyc' => 'تایید احراز هویت',
            'verify-bank-accounts' => 'تایید حساب های بانکی',
            'view-deposits' => 'مشاهده واریزی ها',
            'view-withdraws' => 'مشاهده برداشت ها',
            'view-profile-details' => 'مشاهده جزئیات پروفایل کاربران',
            'view-assets' => 'مشاهده دارایی های کاربران',
            'manage-features-info' => 'مدیریت اطلاعات املاک',
            'manage-pricing-limits' => 'مدیریت محدودیت قیمت گذاری املاک',
            'view-features-prices' => 'مشاهده قیمت املاک',
            'view-priced-features' => 'مشاهده املاک قیمت گذاری شده',
            'view-sold-features' => 'مشاهده زمین های فروخته شده',
            'view-features-trades' => 'مشاهده تبادل املاک',
            'manage-access' => 'مدیریت دسترسی ها',
            'respond-to-citziens-safety-tickets' => 'پاسخ دهی به تیکت های امنیت شهروندان',
            'respond-to-technical-support-tickets' => 'پاسخ دهی به تیکت های پشتیبانی فنی',
            'respond-to-investment-tickets' => 'پاسخ دهی به تیکت های سرمایه گذاری',
            'respond-to-inspection-tickets' => 'پاسخ دهی به تیکت های بازرسی',
            'respond-to-protection-tickets' => 'پاسخ دهی به تیکت های حراست',
            'respond-to-ztb-management-tickets' => 'پاسخ دهی به تیکت های مدیریت کل ز.ت.ب',
            'manage-packages' => 'مدیریت پکیج های فروشگاه',
            'manage-currencies' => 'مدیریت ارزها',
            'manage-dynasty-prizes' => 'مدیریت جوایز سلسله',
            'manage-dynasty-messages' => 'مدیریت پیام های سلسله',
            'manage-dynasty-permissions' => 'مدیریت دسترسی های سلسله',
            'manage-maps' => 'مدیریت نقشه ها',
            'manage-level' => 'مدیریت سطح ها',
            'manage-calendar' => 'مدیریت تقویم',
            'manage-versions' => 'مدیریت ورژن ها',
            'manage-repots' => 'مدیریت گزارشات',
            'manage-system-variables' => 'مدیریت متغییرهای سیستم',
            'manage-tutorials' => 'فیلم های آموزشی',
            'manage-translations' => 'مدیریت ترجمه‌ها',
            'manage-isic-codes' => 'مدیریت کدهای ISIC',
            'manage-challenge' => 'مدیریت چالش پرسش و پاسخ',
            'manage-admin-allowed-ips' => 'مدیریت آی پی های دسترسی بخش Admin',
            'manage-api-allowed-ips' => 'مدیریت آی پی های دسترسی بخش Api',
            'manage-ip-ranges' => 'مدیریت رنج آی پی ها',
            'manage-employee-bank-accounts' => 'مدیریت اطلاعات بانکی کارمندان',
            'manage-employee-documents' => 'مدیریت اسناد کارمندان',
            'manage-employee-info' => 'مدیریت اطلاعات کارمندان',
            'manage-employee-salary' => 'مدیریت حقوق و دستمزد کارمندان',
            'manage-employee-tasks' => 'مدیریت وظایف کارمندان',
            'manage-employee-time-card' => 'مدیریت کارت زمان کارمندان',
        ];
    }

    /**
     * Default permission assignments for module roles.
     *
     * @return array<string, array<int, string>>
     */
    private function rolePermissions(): array
    {
        return [
            'citizens-management' => [
                'view-dashboard',
                'manage-profile',
                'view-registration-info',
                'verify-kyc',
                'verify-bank-accounts',
                'view-deposits',
                'view-withdraws',
                'view-profile-details',
                'view-assets',
            ],
            'access-management' => [
                'view-dashboard',
                'manage-profile',
                'manage-access',
                'manage-translations',
                'manage-isic-codes',
            ],
            'support-management' => [
                'view-dashboard',
                'manage-profile',
                'respond-to-citziens-safety-tickets',
                'respond-to-technical-support-tickets',
                'respond-to-investment-tickets',
                'respond-to-inspection-tickets',
                'respond-to-protection-tickets',
                'respond-to-ztb-management-tickets',
            ],
            'level-management' => [
                'view-dashboard',
                'manage-profile',
                'manage-level',
                'manage-challenge',
            ],
            'tutorials-management' => [
                'view-dashboard',
                'manage-profile',
                'manage-tutorials',
            ],
            'calendar-management' => [
                'view-dashboard',
                'manage-profile',
                'manage-calendar',
            ],
            'versions-management' => [
                'view-dashboard',
                'manage-profile',
                'manage-versions',
            ],
            'reports-management' => [
                'view-dashboard',
                'manage-profile',
                'manage-repots',
            ],
            'ip-management' => [
                'view-dashboard',
                'manage-profile',
                'manage-admin-allowed-ips',
                'manage-api-allowed-ips',
                'manage-ip-ranges',
            ],
        ];
    }

    /**
     * Permissions granted to every non-super admin role.
     *
     * @return array<int, string>
     */
    private function baselinePermissions(): array
    {
        return [
            'view-dashboard',
            'manage-profile',
        ];
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions() as $name => $title) {
            $permission = Permission::query()->firstOrNew([
                'name' => $name,
                'guard_name' => self::GUARD,
            ]);
            $permission->title = $title;
            $permission->save();
        }

        $superAdmin = Role::query()->firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => self::GUARD],
            ['title' => 'مدیریت کل']
        );

        $allPermissions = Permission::query()
            ->where('guard_name', self::GUARD)
            ->pluck('name')
            ->all();

        $superAdmin->syncPermissions($allPermissions);

        foreach ($this->rolePermissions() as $roleName => $permissionNames) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', self::GUARD)
                ->first();

            if (! $role) {
                continue;
            }

            $role->givePermissionTo($permissionNames);
        }

        $baseline = $this->baselinePermissions();

        Role::query()
            ->where('guard_name', self::GUARD)
            ->where('name', '!=', 'super-admin')
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($baseline));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
