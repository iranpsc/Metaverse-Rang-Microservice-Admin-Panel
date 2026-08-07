<?php

namespace Tests\Unit\ActivityLog;

use App\Models\Admin;
use App\Models\User;
use App\Services\ActivityLogCategoryResolver;
use Tests\TestCase;

class ActivityLogCategoryResolverTest extends TestCase
{
    public function test_categories_returns_expected_keys_and_persian_labels(): void
    {
        $categories = ActivityLogCategoryResolver::categories();

        $this->assertIsArray($categories);
        $this->assertArrayHasKey('auth', $categories);
        $this->assertArrayHasKey('dashboard', $categories);
        $this->assertArrayHasKey('other', $categories);
        $this->assertSame('احراز هویت', $categories['auth']);
        $this->assertSame('داشبورد', $categories['dashboard']);
        $this->assertSame('سایر', $categories['other']);
    }

    public function test_label_returns_known_category_label(): void
    {
        $this->assertSame('شهروندان', ActivityLogCategoryResolver::label('citizens'));
        $this->assertSame('احراز هویت', ActivityLogCategoryResolver::label(ActivityLogCategoryResolver::CATEGORY_AUTH));
    }

    public function test_label_falls_back_to_other_for_unknown_category(): void
    {
        $this->assertSame(
            ActivityLogCategoryResolver::categories()['other'],
            ActivityLogCategoryResolver::label('not-a-real-category')
        );
    }

    public function test_resolve_for_model_returns_mapped_category(): void
    {
        $user = new User;
        $admin = new Admin;

        $this->assertSame('citizens', ActivityLogCategoryResolver::resolveForModel($user));
        $this->assertSame('access-management', ActivityLogCategoryResolver::resolveForModel($admin));
    }

    public function test_resolve_for_model_returns_other_for_null(): void
    {
        $this->assertSame('other', ActivityLogCategoryResolver::resolveForModel(null));
    }
}
