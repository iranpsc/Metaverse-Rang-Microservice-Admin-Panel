<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $arrayOfPermissionTitles = [
            'مشاهده اطلاعات ثبت نام',
            'تایید احراز هویت',
            'تایید حساب های بانکی',
            'مشاهده واریزی ها',
            'مشاهده برداشت ها',
            'مشاهده جزئیات پروفایل کاربران',
            'مشاهده دارایی های کاربران',

            'مدیریت اطلاعات املاک',
            'مشاهده قیمت املاک',
            'مشاهده زمین های فروخته شده',
            'مشاهده تبادل املاک',
            'مشاهده املاک قیمت گذاری شده',
            'مدیریت محدودیت قیمت گذاری املاک',

            'مدیریت دسترسی ها',

            'مدیریت اطلاعات کارمندان',
            'مدیریت اطلاعات بانکی کارمندان',
            'مدیریت اسناد کارمندان',
            'مدیریت حقوق و دستمزد کارمندان',
            'مدیریت کارت زمان کارمندان',
            'مدیریت وظایف کارمندان',

            'پاسخ دهی به تیکت های امنیت شهروندان',
            'پاسخ دهی به تیکت های پشتیبانی فنی',
            'پاسخ دهی به تیکت های سرمایه گذاری',
            'پاسخ دهی به تیکت های بازرسی',
            'پاسخ دهی به تیکت های حراست',
            'پاسخ دهی به تیکت های مدیریت کل ز.ت.ب',

            'مدیریت پکیج های فروشگاه',
            'مدیریت ارزها',

            'مدیریت جوایز سلسله',
            'مدیریت پیام های سلسله',
            'مدیریت دسترسی های سلسله',

            'مدیریت رنج آی پی ها',
            'مدیریت آی پی های دسترسی بخش Api',
            'مدیریت آی پی های دسترسی بخش Admin',

            'مدیریت سطح ها',
            'مدیریت نقشه ها',
            'مدیریت تقویم',
            'مدیریت گزارشات',
            'مدیریت متغییرهای سیستم',
        ];
        $arrayOfPermissionNames = [
            'view-registration-info',
            'verify-kyc',
            'verify-bank-accounts',
            'view-deposits',
            'view-withdraws',
            'view-profile-details',
            'view-assets',

            'manage-features-info',
            'view-features-prices',
            'view-sold-features',
            'view-features-trades',
            'view-priced-features',
            'manage-pricing-limits',

            'manage-access',

            'manage-employee-info',
            'manage-employee-bank-accounts',
            'manage-employee-documents',
            'manage-employee-salary',
            'manage-employee-time-card',
            'manage-employee-tasks',

            'respond-to-citziens-safety-tickets',
            'respond-to-technical-support-tickets',
            'respond-to-investment-tickets',
            'respond-to-inspection-tickets',
            'respond-to-protection-tickets',
            'respond-to-ztb-management-tickets',

            'manage-packages',
            'manage-currencies',

            'manage-dynasty-prizes',
            'manage-dynasty-messages',
            'manage-dynasty-permissions',

            'manage-ip-ranges',
            'manage-api-allowed-ips',
            'manage-admin-allowed-ips',

            'manage-level',

            'manage-maps',

            'manage-calendar',
            'manage-repots',
            'manage-system-variables',
        ];

        for($i=0; $i<count($arrayOfPermissionNames); $i++) {
            Permission::create([
                'title' => $arrayOfPermissionTitles[$i],
                'name' => $arrayOfPermissionNames[$i],
                'guard_name' => 'admin',
            ]);
        }
    }
}
