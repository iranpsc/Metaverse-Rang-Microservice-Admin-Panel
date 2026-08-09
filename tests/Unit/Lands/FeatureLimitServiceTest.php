<?php

namespace Tests\Unit\Lands;

use App\Models\FeatureLimit;
use App\Models\FeatureProperties;
use App\Services\Lands\FeatureLimitService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Morilog\Jalali\Jalalian;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesFeatureLimitsApiSchema;
use Tests\TestCase;

class FeatureLimitServiceTest extends TestCase
{
    use CreatesFeatureLimitsApiSchema;

    private FeatureLimitService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFeatureLimitsApiSchema();
        $this->service = new FeatureLimitService;
    }

    // -------------------------------------------------------------------------
    // getPaginated
    // -------------------------------------------------------------------------

    public function test_get_paginated_returns_empty_collection_when_no_limits_exist(): void
    {
        $result = $this->service->getPaginated(10, 1);

        $this->assertSame([], $result['feature_limits']);
        $this->assertSame(1, $result['pagination']['current_page']);
        $this->assertSame(0, $result['pagination']['total']);
    }

    public function test_get_paginated_adds_expired_flag_and_shamsi_dates(): void
    {
        $active = $this->createFeatureLimit([
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        $expired = $this->createFeatureLimit([
            'start_date' => now()->subDays(20)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
        ]);

        $result = $this->service->getPaginated(10, 1);
        $items = collect($result['feature_limits'])->keyBy('id');

        $this->assertFalse($items[$active->id]->expired);
        $this->assertTrue($items[$expired->id]->expired);
        $this->assertSame(
            Jalalian::fromCarbon(Carbon::parse($active->start_date))->format('Y/m/d'),
            $items[$active->id]->start_date_shamsi
        );
        $this->assertSame(
            Jalalian::fromCarbon(Carbon::parse($active->end_date))->format('Y/m/d'),
            $items[$active->id]->end_date_shamsi
        );
    }

    public function test_get_paginated_paginates_results(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->createFeatureLimit(['title' => "Limit {$i}"]);
        }

        $pageOne = $this->service->getPaginated(3, 1);
        $pageTwo = $this->service->getPaginated(3, 2);

        $this->assertCount(3, $pageOne['feature_limits']);
        $this->assertCount(3, $pageTwo['feature_limits']);
        $this->assertSame(1, $pageOne['pagination']['current_page']);
        $this->assertSame(2, $pageTwo['pagination']['current_page']);
        $this->assertSame(7, $pageOne['pagination']['total']);
        $this->assertSame(3, $pageOne['pagination']['last_page']);
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_persists_feature_limit_with_converted_dates(): void
    {
        $this->seedStoreRangeProperties();

        $startJalali = Jalalian::now()->format('Y/m/d');
        $endJalali = Jalalian::now()->addMonths(2)->format('Y/m/d');

        $limit = $this->service->create($this->validStorePayload([
            'title' => 'Service Created Limit',
            'start_date' => $startJalali,
            'end_date' => $endJalali,
        ]));

        $this->assertInstanceOf(FeatureLimit::class, $limit);
        $this->assertSame('Service Created Limit', $limit->title);
        $this->assertSame(
            Jalalian::fromFormat('Y/m/d', $startJalali)->toCarbon()->toDateString(),
            $limit->start_date->toDateString()
        );
        $this->assertSame(
            Jalalian::fromFormat('Y/m/d', $endJalali)->toCarbon()->toDateString(),
            $limit->end_date->toDateString()
        );
    }

    public function test_create_zeros_optional_numeric_fields_when_limits_disabled(): void
    {
        $this->seedStoreRangeProperties();

        $limit = $this->service->create($this->validStorePayload([
            'price_limit' => false,
            'price' => 9000,
            'individual_buy_limit' => false,
            'individual_buy_count' => 12,
        ]));

        $this->assertSame(0.0, (float) $limit->price);
        $this->assertSame(0, $limit->individual_buy_count);
    }

    #[DataProvider('limitedRgbProvider')]
    public function test_create_applies_limited_rgb_by_karbari(string $karbari, string $expectedRgb): void
    {
        $this->createFeaturePropertyRange('FL', 1, 1, 1, [
            'karbari' => $karbari,
            'rgb' => 'd',
        ]);

        $this->service->create($this->validStorePayload([
            'start_id' => 'FL-001',
            'end_id' => 'FL-001',
            'verified_kyc_limit' => true,
        ]));

        $this->assertSame($expectedRgb, FeatureProperties::query()->findOrFail('FL-001')->rgb);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function limitedRgbProvider(): array
    {
        return [
            'residential' => ['m', 'g'],
            'commercial' => ['t', 'n'],
            'educational' => ['a', 'uu'],
            'default' => ['x', 'rgb'],
        ];
    }

    #[DataProvider('sellLimitedRgbProvider')]
    public function test_create_applies_sell_limited_rgb_by_karbari(string $karbari, string $expectedRgb): void
    {
        $this->createFeaturePropertyRange('FL', 1, 1, 1, [
            'karbari' => $karbari,
            'rgb' => 'd',
        ]);

        $this->service->create($this->validStorePayload([
            'start_id' => 'FL-001',
            'end_id' => 'FL-001',
            'not_sellable' => true,
        ]));

        $this->assertSame($expectedRgb, FeatureProperties::query()->findOrFail('FL-001')->rgb);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function sellLimitedRgbProvider(): array
    {
        return [
            'residential' => ['m', 'f'],
            'commercial' => ['t', 'm'],
            'educational' => ['a', 'tt'],
            'default' => ['x', 'rgb'],
        ];
    }

    public function test_create_applies_limits_only_to_system_owned_features_in_range(): void
    {
        $inRange = $this->createFeaturePropertyRange('FL', 1, 3, 1, ['rgb' => 'd']);
        $outside = $this->createLandWithProperties([], [
            'id' => 'FL-010',
            'id_prefix' => 'FL',
            'id_postfix' => 10,
            'rgb' => 'd',
        ]);
        $nonSystem = $this->seedNonSystemOwnedProperty('FL', 4);

        $this->service->create($this->validStorePayload([
            'verified_kyc_limit' => true,
        ]));

        $this->assertSame('g', $inRange[0]->fresh()->rgb);
        $this->assertSame('g', $inRange[2]->fresh()->rgb);
        $this->assertSame('d', $outside->fresh()->rgb);
        $this->assertSame('d', $nonSystem->fresh()->rgb);
    }

    public function test_create_sets_stability_when_price_limit_is_enabled(): void
    {
        $this->createFeaturePropertyRange('FL', 1, 1, 1);

        $this->service->create($this->validStorePayload([
            'start_id' => 'FL-001',
            'end_id' => 'FL-001',
            'price_limit' => true,
            'price' => 4321,
        ]));

        $this->assertSame(4321.0, (float) FeatureProperties::query()->findOrFail('FL-001')->stability);
    }

    // -------------------------------------------------------------------------
    // delete
    // -------------------------------------------------------------------------

    #[DataProvider('defaultRgbProvider')]
    public function test_delete_restores_default_rgb_by_karbari(string $karbari, string $expectedRgb): void
    {
        $property = $this->createFeaturePropertyRange('FL', 1, 1, 1, [
            'karbari' => $karbari,
            'rgb' => 'd',
            'density' => 3,
            'area' => 40,
            'stability' => 120,
        ])[0];

        $limit = $this->createFeatureLimit([
            'start_id' => 'FL-001',
            'end_id' => 'FL-001',
            'verified_kyc_limit' => true,
        ]);

        $property->update(['rgb' => 'g', 'stability' => 999]);

        $this->service->delete($limit->id);

        $property->refresh();

        $this->assertSame($expectedRgb, $property->rgb);
        $this->assertSame(120.0, (float) $property->stability);
        $this->assertDatabaseMissing('feature_limits', ['id' => $limit->id]);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function defaultRgbProvider(): array
    {
        return [
            'residential' => ['m', 'd'],
            'commercial' => ['t', 'k'],
            'educational' => ['a', 'r'],
            'default' => ['x', 'rgb'],
        ];
    }

    public function test_create_applies_additional_limit_flags_to_system_owned_features(): void
    {
        $cases = [
            'dynasty_owner_limit',
            'verified_bank_account_limit',
            'under_18_limit',
            'more_than_18_limit',
        ];

        foreach ($cases as $index => $flag) {
            $prefix = 'LX'.$index;
            $this->createFeaturePropertyRange($prefix, 1, 1, 1, [
                'karbari' => 'm',
                'rgb' => 'd',
            ]);

            $this->service->create($this->validStorePayload([
                'start_id' => $prefix.'-001',
                'end_id' => $prefix.'-001',
                $flag => true,
            ]));

            $this->assertSame(
                'g',
                FeatureProperties::query()->findOrFail($prefix.'-001')->rgb,
                "Expected limited rgb for flag {$flag}"
            );
        }
    }

    public function test_delete_throws_when_limit_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->delete(999999);
    }

    public function test_delete_does_not_restore_features_outside_range(): void
    {
        $inside = $this->createFeaturePropertyRange('FL', 1, 1, 1, [
            'rgb' => 'd',
            'density' => 2,
            'area' => 10,
        ])[0];
        $outside = $this->createLandWithProperties([], [
            'id' => 'FL-005',
            'id_prefix' => 'FL',
            'id_postfix' => 5,
            'rgb' => 'g',
            'density' => 2,
            'area' => 10,
            'stability' => 999,
        ]);

        $limit = $this->createFeatureLimit([
            'start_id' => 'FL-001',
            'end_id' => 'FL-001',
        ]);

        $inside->update(['rgb' => 'g', 'stability' => 999]);

        $this->service->delete($limit->id);

        $this->assertSame('d', $inside->fresh()->rgb);
        $this->assertSame('g', $outside->fresh()->rgb);
        $this->assertSame(999.0, (float) $outside->fresh()->stability);
    }
}
