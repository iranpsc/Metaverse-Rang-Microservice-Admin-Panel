<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ActivityLogCategoryResolver
{
    public const CATEGORY_AUTH = 'auth';

    public const CATEGORY_DASHBOARD = 'dashboard';

    /**
     * Sidebar category definitions (id => Persian label).
     *
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'dashboard' => 'داشبورد',
            'citizens' => 'شهروندان',
            'features' => 'زمین ها',
            'access-management' => 'مدیریت دسترسی ها',
            'support' => 'پشتیبانی',
            'store' => 'فروشگاه',
            'dynasty' => 'سلسله',
            'map-management' => 'مدیریت نقشه ها',
            'levels' => 'مدیریت سطوح',
            'calendar' => 'تقویم',
            'versions' => 'ورژن ها',
            'reports' => 'گزارشات کاربران',
            'system-variables' => 'متغیرهای سیستم',
            'challenge' => 'چالش پرسش و پاسخ',
            'tutorials' => 'فیلم های آموزشی',
            'translations' => 'ترجمه',
            'isic-codes' => 'کدهای ISIC',
            self::CATEGORY_AUTH => 'احراز هویت',
            'other' => 'سایر',
        ];
    }

    /**
     * @var array<string, string>
     */
    protected static array $modelMap = [
        \App\Models\User::class => 'citizens',
        \App\Models\Kyc::class => 'citizens',
        \App\Models\KycError::class => 'citizens',
        \App\Models\KycVerifyText::class => 'citizens',
        \App\Models\BankAccount::class => 'citizens',
        \App\Models\Wallet::class => 'citizens',
        \App\Models\Transaction::class => 'citizens',
        \App\Models\Payment::class => 'citizens',
        \App\Models\Referral::class => 'citizens',
        \App\Models\ReferralOrderHistory::class => 'citizens',
        \App\Models\LockedAsset::class => 'citizens',
        \App\Models\FirstOrder::class => 'citizens',
        \App\Models\FirstPurchase::class => 'citizens',

        \App\Models\Land::class => 'features',
        \App\Models\Feature::class => 'features',
        \App\Models\FeatureLimit::class => 'features',
        \App\Models\FeatureProperties::class => 'features',
        \App\Models\FeatureImage::class => 'features',
        \App\Models\BuyFeatureRequest::class => 'features',
        \App\Models\SellFeatureRequest::class => 'features',
        \App\Models\Order::class => 'features',
        \App\Models\Trade::class => 'features',
        \App\Models\Coordinate::class => 'features',
        \App\Models\Geometry::class => 'features',
        \App\Models\Image::class => 'features',

        \App\Models\Admin::class => 'access-management',
        Role::class => 'access-management',
        Permission::class => 'access-management',

        \App\Models\Ticket::class => 'support',
        \App\Models\TicketResponse::class => 'support',

        \App\Models\Report::class => 'reports',

        \App\Models\Map::class => 'map-management',
        \App\Models\Calendar::class => 'calendar',
        \App\Models\SystemVariable::class => 'system-variables',
        \App\Models\Variable::class => 'system-variables',
        \App\Models\VariableChangeLog::class => 'system-variables',
        \App\Models\Option::class => 'system-variables',
        \App\Models\Setting::class => 'system-variables',
        \App\Models\GeneralSetting::class => 'system-variables',

        \App\Models\Video::class => 'tutorials',
        \App\Models\VideoCategory::class => 'tutorials',
        \App\Models\VideoSubCategory::class => 'tutorials',

        \App\Models\IsicCode::class => 'isic-codes',

        \App\Models\Like::class => 'other',
        \App\Models\Dislike::class => 'other',
        \App\Models\View::class => 'other',
        \App\Models\Note::class => 'other',
        \App\Models\Interaction::class => 'other',
        \App\Models\Ip::class => 'other',
        \App\Models\Crs::class => 'other',
        \App\Models\CrsProperty::class => 'other',
        \App\Models\Comission::class => 'other',
    ];

    public static function resolveForModel(?Model $model): string
    {
        if ($model === null) {
            return 'other';
        }

        $class = $model::class;

        if (isset(self::$modelMap[$class])) {
            return self::$modelMap[$class];
        }

        $basename = class_basename($class);

        if (str_contains($basename, 'Dynasty')) {
            return 'dynasty';
        }

        if (str_contains($basename, 'Level')) {
            return 'levels';
        }

        if (str_contains($basename, 'Translation')) {
            return 'translations';
        }

        if (str_contains($basename, 'Challenge') || str_contains($basename, 'Question')) {
            return 'challenge';
        }

        return 'other';
    }

    public static function label(string $category): string
    {
        return self::categories()[$category] ?? self::categories()['other'];
    }
}
