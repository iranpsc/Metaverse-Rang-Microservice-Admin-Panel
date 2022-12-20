<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//         \App\Models\User::factory()->count(1)->create();
//        DB::table('admins')->insert([
//            'name' => fake()->name(),
//            'email' => fake()->safeEmail(),
//            'email_verified_at' => now(),
//            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
//            'phone' => fake()->phoneNumber,
//            'role' => 'admin',
//            'access_password' => random_int(100, 900),
//            'remember_token' => Str::random(10),
//        ]);
//        Admin::create([
//            'name' => fake()->name,
//            'email' => fake()->email,
//            'password' => bcrypt('123123123'),
//            'phone' => '09919986371',
//            'access_password' => bcrypt('123123123'),
//        ]);
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
//        Role::truncate();
//        Role::insert([
//            [
//                'name' => 'admin',
//                'title' => 'مدیر',
//                'guard_name' => 'web',
//            ],
//            [
//                'name' => 'super-admin',
//                'title' => 'مدیر کل',
//                'guard_name' => 'web',
//            ],
//            [
//                'name' => 'support',
//                'title' => 'پشتیبانی',
//                'guard_name' => 'web',
//            ],
//        ]);
//
//        $admin = Admin::where('email','sa204@yahoo.com')->first();
//        $admin->update([
//            'password' => Hash::make('123123123'),
//            'access_password' => Hash::make('123123123'),
//        ]);
        Admin::truncate();
        Admin::create([
            'name' => fake()->name,
            'email' => 'sa204@yahoo.com',
            'password' => bcrypt('123123123'),
            'phone' => '09919986371',
            'access_password' => bcrypt('123123123'),
        ]);
//        Admin::where('email','!=','sa204@yahoo.com')->delete();
        Role::truncate();
        Permission::truncate();
        DB::table('model_has_roles')->truncate();
        Role::insert([
           [
               'name' => 'Super-Admin',
               'title' => 'مدیریت کل',
               'guard_name' => 'web'
           ],
            [
                'name' => 'Citizen-Management',
                'title' => 'مدیریت شهروندان',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Access-Management',
                'title' => 'مدیریت دسترسی ها',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Lands-Management',
                'title' => 'مدیریت زمین ها',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Employee-Management',
                'title' => 'مدیریت کارکنان',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Support-Management',
                'title' => 'مدیریت پشتیبانی',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Shop-Management',
                'title' => 'مدیریت فروشگاه',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Challenge-Management',
                'title' => 'مدیریت چالش ها',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Dynasty-Management',
                'title' => 'مدیریت سلسله',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Analyze-Management',
                'title' => 'مدیریت آمار سراری',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Maps-Management',
                'title' => 'مدیریت نقشه ها',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Level-Management',
                'title' => 'مدیریت سطح',
                'guard_name' => 'web'
            ],
            [
                'name' => 'IP-Management',
                'title' => 'مدیریت ای پی ها',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Calendar-Management',
                'title' => 'مدیریت تقویم',
                'guard_name' => 'web'
            ],
            [
                'name' => 'User-Reports-Management',
                'title' => 'مدیریت گزارشات کاربران',
                'guard_name' => 'web'
            ],
            [
                'name' => 'System-Variables-Management',
                'title' => 'مدیریت متغیر های سیستم',
                'guard_name' => 'web'
            ],
        ]);

        Permission::insert([
           [
               'name' => 'Registration-Info',
               'title' => 'اطلاعات ثبت نام',
               'guard_name' => 'web',
           ],
            [
                'name' => 'Authorize',
                'title' => 'احراز هویت',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Bank-Account',
                'title' => 'اطلاعات بانکی',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Deposits',
                'title' => 'واریز',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Withdraw',
                'title' => 'برداشت',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Profile-Info',
                'title' => 'اطلاعات پروفایل',
                'guard_name' => 'web',
            ],
            [
                'name' => 'User-Assets',
                'title' => 'دارایی های کاربر',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Lands-List',
                'title' => 'لیست زمین ها',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Lands-Price',
                'title' => 'قیمت زمین ها',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Import-Lands',
                'title' => 'بارگزاری زمین',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Sold-Lands',
                'title' => 'زمین های فروخته شده',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Trade-Land',
                'title' => 'معامله زمین',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Set-Land-Price',
                'title' => 'ثبت قیمت زمین',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Enter-Land-Price',
                'title' => 'قیمت ورودی زمین',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Land-Entry-Price-Limit',
                'title' => 'محدودیت قیمت ورودی زمین',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Employee-Actual-Info',
                'title' => 'اطلاعات حقیقی کارکنان',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Employee-Bank',
                'title' => 'اطلاعات بانکی کارکنان',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Employee-Documents',
                'title' => 'مدارک کارکنان',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Employee-Salary',
                'title' => 'درآمد کارکنان',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Employee-Time-Card',
                'title' => 'مدیریت زمان کارکنان',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Employee-Duties',
                'title' => 'وظایف کارکنان',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Technical-Support',
                'title' => 'پشتیبانی فنی',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Citizen-Security-Support',
                'title' => 'پشتیبانی امنیت شهروندان',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Investment-Support',
                'title' => 'پشتیبانی سرمایه گذاری',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Inspection-Support',
                'title' => 'پشتیبانی بازرسی',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Protection-Support',
                'title' => 'پشتیبانی حراست',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Co-Management-Support',
                'title' => 'پشیبیانی مدیریت کل',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Shop-Packages',
                'title' => 'پکیج های فروشگاه',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Shop-Packages-Change-History',
                'title' => 'تاریخچه تغییر قیمت پکیج های فروشگاه',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Shop-Currencies',
                'title' => 'ارز های فروشگاه',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Shop-Change-Currencies-Price-History',
                'title' => 'تاریخچه تغییر قیمت ارز های فروشگاه',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Dynasty-Prizes',
                'title' => 'جوایز سلسله',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Dynasty-Messages',
                'title' => 'پیام های سلسله',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Upload-Maps',
                'title' => 'بارگذاری نقشه ها',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Define-Level',
                'title' => 'تعریف سطح',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Show-Level',
                'title' => 'نمایش سطح',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Edit-Level',
                'title' => 'ویرایش سطح',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Delete-Level',
                'title' => 'حذف سطح',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Define-Level-Prizes',
                'title' => 'تعریف جوایز سطح',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Define-Range-IP',
                'title' => 'تعریف رنج ای پی',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Allowed-API-IPs',
                'title' => 'آی پی های مجاز ای پی ای',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Allowed-Admin-IPs',
                'title' => 'آی پی های مجاز پنل ادمین',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Blocked-IPs',
                'title' => 'آی پی های بلاک شده',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Define-Role',
                'title' => 'تعریف نقش',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Define-Permission',
                'title' => 'تعریف دسترسی',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Assign-Permission-To-Role',
                'title' => 'تعریف دسترسی برای نقش',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Delete-Role',
                'title' => 'حذف نقش',
                'guard_name' => 'web',
            ],
        ]);
        $admin = Admin::where('email','sa204@yahoo.com')->first();
        $role = Role::where('name','Super-Admin')->first();
        $admin->assignRole($role);
    }
}
