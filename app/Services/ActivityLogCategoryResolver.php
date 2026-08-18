<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\BankAccount;
use App\Models\BuyFeatureRequest;
use App\Models\Calendar;
use App\Models\Comission;
use App\Models\Coordinate;
use App\Models\Crs;
use App\Models\CrsProperty;
use App\Models\Dislike;
use App\Models\Feature;
use App\Models\FeatureImage;
use App\Models\FeatureLimit;
use App\Models\FeatureProperties;
use App\Models\FirstOrder;
use App\Models\FirstPurchase;
use App\Models\GeneralSetting;
use App\Models\Geometry;
use App\Models\Image;
use App\Models\Interaction;
use App\Models\IsicCode;
use App\Models\Kyc;
use App\Models\KycError;
use App\Models\KycVerifyText;
use App\Models\Land;
use App\Models\Like;
use App\Models\LockedAsset;
use App\Models\Map;
use App\Models\Note;
use App\Models\Option;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\ReferralOrderHistory;
use App\Models\Report;
use App\Models\SellFeatureRequest;
use App\Models\Setting;
use App\Models\SystemVariable;
use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Variable;
use App\Models\VariableChangeLog;
use App\Models\Video;
use App\Models\VideoCategory;
use App\Models\VideoSubCategory;
use App\Models\View;
use App\Models\Wallet;
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
        User::class => 'citizens',
        Kyc::class => 'citizens',
        KycError::class => 'citizens',
        KycVerifyText::class => 'citizens',
        BankAccount::class => 'citizens',
        Wallet::class => 'citizens',
        Transaction::class => 'citizens',
        Payment::class => 'citizens',
        Referral::class => 'citizens',
        ReferralOrderHistory::class => 'citizens',
        LockedAsset::class => 'citizens',
        FirstOrder::class => 'citizens',
        FirstPurchase::class => 'citizens',

        Land::class => 'features',
        Feature::class => 'features',
        FeatureLimit::class => 'features',
        FeatureProperties::class => 'features',
        FeatureImage::class => 'features',
        BuyFeatureRequest::class => 'features',
        SellFeatureRequest::class => 'features',
        Order::class => 'features',
        Trade::class => 'features',
        Coordinate::class => 'features',
        Geometry::class => 'features',
        Image::class => 'features',

        Admin::class => 'access-management',
        Role::class => 'access-management',
        Permission::class => 'access-management',

        Ticket::class => 'support',
        TicketResponse::class => 'support',

        Report::class => 'reports',

        Map::class => 'map-management',
        Calendar::class => 'calendar',
        SystemVariable::class => 'system-variables',
        Variable::class => 'system-variables',
        VariableChangeLog::class => 'system-variables',
        Option::class => 'system-variables',
        Setting::class => 'system-variables',
        GeneralSetting::class => 'system-variables',

        Video::class => 'tutorials',
        VideoCategory::class => 'tutorials',
        VideoSubCategory::class => 'tutorials',

        IsicCode::class => 'isic-codes',

        Like::class => 'other',
        Dislike::class => 'other',
        View::class => 'other',
        Note::class => 'other',
        Interaction::class => 'other',
        Crs::class => 'other',
        CrsProperty::class => 'other',
        Comission::class => 'other',
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
