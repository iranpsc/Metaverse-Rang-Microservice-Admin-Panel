<?php

namespace Database\Factories\Feature;

use App\Models\Feature\FeaturePricingLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeaturePricingLimit>
 */
class FeaturePricingLimitFactory extends Factory
{
    protected $model = FeaturePricingLimit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_price_limit' => fake()->numberBetween(100, 10000),
            'under_eighteen_price_limit' => fake()->numberBetween(50, 5000),
        ];
    }
}
