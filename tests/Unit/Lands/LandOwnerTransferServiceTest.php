<?php

namespace Tests\Unit\Lands;

use App\Services\Lands\LandOwnerTransferService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\Concerns\CreatesLandsApiSchema;
use Tests\TestCase;

class LandOwnerTransferServiceTest extends TestCase
{
    use CreatesLandsApiSchema;

    private LandOwnerTransferService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLandsApiSchema();
        $this->service = new LandOwnerTransferService;
    }

    // -------------------------------------------------------------------------
    // getOptions — lands
    // -------------------------------------------------------------------------

    public function test_get_options_lands_returns_transferable_and_disabled_entries(): void
    {
        $systemFeature = $this->createFeature(['owner_id' => 1]);
        $systemProperty = $this->createFeatureProperties($systemFeature, ['id' => 'SVC-SYS-001']);

        $owner = $this->createCitizenUser(['name' => 'Service Owner']);
        $ownedFeature = $this->createFeature(['owner_id' => $owner->id]);
        $ownedProperty = $this->createFeatureProperties($ownedFeature, ['id' => 'SVC-OWN-002']);

        $result = $this->service->getOptions('lands', '', 1, 20);

        $this->assertInstanceOf(Collection::class, $result['options']);
        $this->assertArrayHasKey('pagination', $result);

        $options = $result['options']->keyBy('value');

        $this->assertFalse($options[$systemFeature->id]['disabled']);
        $this->assertSame($systemProperty->id, $options[$systemFeature->id]['label']);
        $this->assertTrue($options[$ownedFeature->id]['disabled']);
        $this->assertStringContainsString($ownedProperty->id, $options[$ownedFeature->id]['label']);
        $this->assertStringContainsString('Service Owner', $options[$ownedFeature->id]['label']);
    }

    public function test_get_options_lands_filters_by_property_id_search(): void
    {
        $match = $this->createLandWithProperties([], ['id' => 'FILTER-LAND-001']);
        $this->createLandWithProperties([], ['id' => 'OTHER-LAND-002']);

        $result = $this->service->getOptions('lands', 'FILTER-LAND', 1, 20);

        $this->assertSame(1, $result['pagination']['total']);
        $this->assertSame($match->feature_id, $result['options']->first()['value']);
    }

    public function test_get_options_lands_paginates_results(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createLandWithProperties([], ['id' => sprintf('PAG-LAND-%03d', $i)]);
        }

        $result = $this->service->getOptions('lands', '', 2, 2);

        $this->assertSame(2, $result['pagination']['current_page']);
        $this->assertSame(2, $result['pagination']['per_page']);
        $this->assertSame(5, $result['pagination']['total']);
        $this->assertTrue($result['pagination']['more']);
        $this->assertCount(2, $result['options']);
    }

    // -------------------------------------------------------------------------
    // getOptions — users
    // -------------------------------------------------------------------------

    public function test_get_options_users_excludes_system_user_and_formats_labels(): void
    {
        $user = $this->createCitizenUser([
            'name' => 'Target User',
            'code' => 'USR999',
        ]);

        $result = $this->service->getOptions('users', '', 1, 20);

        $values = $result['options']->pluck('value')->all();

        $this->assertNotContains(1, $values);
        $this->assertContains($user->id, $values);

        $option = $result['options']->firstWhere('value', $user->id);
        $this->assertSame('Target User (USR999)', $option['label']);
        $this->assertFalse($option['disabled']);
    }

    public function test_get_options_users_filters_by_name_code_or_email(): void
    {
        $byEmail = $this->createCitizenUser([
            'name' => 'Email Match',
            'code' => '0001',
            'email' => 'unique-email@example.com',
        ]);
        $this->createCitizenUser([
            'name' => 'Other User',
            'code' => '0002',
            'email' => 'other@example.com',
        ]);

        $result = $this->service->getOptions('users', 'unique-email@example.com', 1, 20);

        $this->assertSame(1, $result['pagination']['total']);
        $this->assertSame($byEmail->id, $result['options']->first()['value']);
    }

    public function test_get_options_users_paginates_results(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $this->createCitizenUser([
                'name' => "Paged User {$i}",
                'email' => "paged{$i}@example.com",
                'code' => (string) (5000 + $i),
            ]);
        }

        $result = $this->service->getOptions('users', '', 2, 2);

        $this->assertSame(2, $result['pagination']['current_page']);
        $this->assertSame(4, $result['pagination']['total']);
        $this->assertCount(2, $result['options']);
    }

    public function test_get_options_throws_for_unsupported_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported owner transfer option type [invalid].');

        $this->service->getOptions('invalid', '', 1, 20);
    }

    // -------------------------------------------------------------------------
    // transferOwner
    // -------------------------------------------------------------------------

    public function test_transfer_owner_updates_feature_owner_for_system_owned_land(): void
    {
        $feature = $this->createFeature(['owner_id' => 1]);
        $this->createFeatureProperties($feature, ['id' => 'SVC-TRANSFER-001']);
        $newOwner = $this->createCitizenUser(['name' => 'New Service Owner']);

        $this->service->transferOwner($feature->id, $newOwner->id);

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'owner_id' => $newOwner->id,
        ]);
    }

    public function test_transfer_owner_throws_when_land_is_not_system_owned(): void
    {
        $owner = $this->createCitizenUser(['name' => 'Existing Owner']);
        $feature = $this->createFeature(['owner_id' => $owner->id]);
        $this->createFeatureProperties($feature, ['id' => 'SVC-NOT-SYS-001']);
        $newOwner = $this->createCitizenUser(['name' => 'Blocked Owner']);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('فقط زمین‌های بدون مالک کاربر قابل انتقال هستند');

        try {
            $this->service->transferOwner($feature->id, $newOwner->id);
        } finally {
            $this->assertDatabaseHas('features', [
                'id' => $feature->id,
                'owner_id' => $owner->id,
            ]);
        }
    }

    public function test_transfer_owner_throws_model_not_found_for_missing_feature(): void
    {
        $newOwner = $this->createCitizenUser();

        $this->expectException(ModelNotFoundException::class);

        $this->service->transferOwner(999999, $newOwner->id);
    }
}
