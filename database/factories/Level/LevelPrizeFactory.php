<?php

namespace Database\Factories\Level;

use App\Models\Level\Level;
use App\Models\Level\LevelPrize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LevelPrize>
 */
class LevelPrizeFactory extends Factory
{
    protected $model = LevelPrize::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'psc' => fake()->numberBetween(0, 10000),
            'yellow' => fake()->numberBetween(0, 100),
            'blue' => fake()->numberBetween(0, 100),
            'red' => fake()->numberBetween(0, 100),
            'effect' => fake()->numberBetween(0, 1000),
            'satisfaction' => fake()->randomFloat(4, 0, 100),
        ];
    }
}
