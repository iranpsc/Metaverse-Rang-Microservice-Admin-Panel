<?php

namespace Tests\Concerns;

use App\Models\Feature\FeaturePricingLimit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesFeaturePricingLimitsApiSchema
{
    use CreatesAuthApiSchema;

    protected function setUpFeaturePricingLimitsApiSchema(): void
    {
        $this->setUpAuthApiSchema();
        $this->createFeaturePricingLimitsTable();
        $this->resetFeaturePricingLimitsTable();
    }

    protected function resetFeaturePricingLimitsTable(): void
    {
        if (! Schema::hasTable('feature_pricing_limits')) {
            return;
        }

        FeaturePricingLimit::query()->delete();
    }

    private function createFeaturePricingLimitsTable(): void
    {
        if (Schema::hasTable('feature_pricing_limits')) {
            return;
        }

        Schema::create('feature_pricing_limits', function (Blueprint $table) {
            $table->id();
            $table->integer('public_price_limit')->default(0);
            $table->integer('under_eighteen_price_limit')->default(0);
            $table->timestamps();
        });
    }

    protected function createFeaturePricingLimit(array $overrides = []): FeaturePricingLimit
    {
        return FeaturePricingLimit::create(array_merge([
            'public_price_limit' => 1000,
            'under_eighteen_price_limit' => 500,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'public_price_limit' => 2500,
            'under_eighteen_price_limit' => 1200,
        ], $overrides);
    }
}
