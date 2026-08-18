<?php

namespace Tests\Concerns;

use App\Models\FeatureLimit;
use App\Models\FeatureProperties;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;

trait CreatesFeatureLimitsApiSchema
{
    use CreatesLandsApiSchema;

    protected function setUpFeatureLimitsApiSchema(): void
    {
        $this->setUpLandsApiSchema();
        $this->ensureFeaturePropertiesStabilityColumn();
        $this->createFeatureLimitsTable();
    }

    private function ensureFeaturePropertiesStabilityColumn(): void
    {
        if (! Schema::hasTable('feature_properties')) {
            return;
        }

        if (Schema::hasColumn('feature_properties', 'stability')) {
            return;
        }

        Schema::table('feature_properties', function (Blueprint $table) {
            $table->decimal('stability', 16, 4)->nullable();
        });
    }

    private function createFeatureLimitsTable(): void
    {
        if (Schema::hasTable('feature_limits')) {
            return;
        }

        Schema::create('feature_limits', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('start_id');
            $table->string('end_id')->nullable();
            $table->boolean('verified_kyc_limit')->default(false);
            $table->boolean('verified_bank_account_limit')->default(false);
            $table->boolean('not_sellable')->default(false);
            $table->boolean('under_18_limit')->default(false);
            $table->boolean('more_than_18_limit')->default(false);
            $table->boolean('dynasty_owner_limit')->default(false);
            $table->boolean('price_limit')->default(false);
            $table->unsignedBigInteger('price')->default(0);
            $table->boolean('individual_buy_limit')->default(false);
            $table->integer('individual_buy_count')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('expired')->default(false);
            $table->timestamps();
        });
    }

    protected function createFeatureLimit(array $overrides = []): FeatureLimit
    {
        $startDate = $overrides['start_date'] ?? now()->subDays(5)->toDateString();
        $endDate = $overrides['end_date'] ?? now()->addDays(30)->toDateString();
        unset($overrides['start_date'], $overrides['end_date']);

        return FeatureLimit::create(array_merge([
            'title' => 'Test Limit',
            'start_id' => 'FL-001',
            'end_id' => 'FL-010',
            'verified_kyc_limit' => false,
            'verified_bank_account_limit' => false,
            'not_sellable' => false,
            'under_18_limit' => false,
            'more_than_18_limit' => false,
            'dynasty_owner_limit' => false,
            'price_limit' => false,
            'price' => 0,
            'individual_buy_limit' => false,
            'individual_buy_count' => 0,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validStorePayload(array $overrides = []): array
    {
        $startDate = Jalalian::now()->format('Y/m/d');
        $endDate = Jalalian::now()->addMonths(3)->format('Y/m/d');

        return array_merge([
            'verified_kyc_limit' => false,
            'verified_bank_account_limit' => false,
            'not_sellable' => false,
            'under_18_limit' => false,
            'more_than_18_limit' => false,
            'dynasty_owner_limit' => false,
            'title' => 'محدودیت تست',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_id' => 'FL-001',
            'end_id' => 'FL-003',
            'price_limit' => false,
            'price' => 0,
            'individual_buy_limit' => false,
            'individual_buy_count' => 0,
        ], $overrides);
    }

    /**
     * @return array<int, FeatureProperties>
     */
    protected function createFeaturePropertyRange(
        string $prefix,
        int $startPostfix,
        int $endPostfix,
        int $ownerId = 1,
        array $propertyOverrides = []
    ): array {
        $properties = [];

        for ($postfix = $startPostfix; $postfix <= $endPostfix; $postfix++) {
            $id = sprintf('%s-%03d', $prefix, $postfix);
            $feature = $this->createFeature(['owner_id' => $ownerId]);

            $properties[] = $this->createFeatureProperties($feature, array_merge([
                'id' => $id,
                'id_prefix' => $prefix,
                'id_postfix' => $postfix,
                'karbari' => 'm',
                'rgb' => 'd',
                'density' => 5,
                'area' => 100,
                'stability' => 500,
            ], $propertyOverrides));
        }

        return $properties;
    }

    protected function seedStoreRangeProperties(string $prefix = 'FL'): array
    {
        return $this->createFeaturePropertyRange($prefix, 1, 3);
    }

    protected function seedNonSystemOwnedProperty(string $prefix = 'FL', int $postfix = 2): FeatureProperties
    {
        $owner = $this->createCitizenUser();

        return $this->createFeatureProperties(
            $this->createFeature(['owner_id' => $owner->id]),
            [
                'id' => sprintf('%s-%03d', $prefix, $postfix),
                'id_prefix' => $prefix,
                'id_postfix' => $postfix,
                'karbari' => 'm',
                'rgb' => 'd',
            ]
        );
    }
}
