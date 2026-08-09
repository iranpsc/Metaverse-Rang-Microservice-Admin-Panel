<?php

namespace Database\Factories;

use App\Models\IsicCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IsicCode>
 */
class IsicCodeFactory extends Factory
{
    protected $model = IsicCode::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('########'),
            'name' => fake()->unique()->words(3, true),
            'verified' => false,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'verified' => true,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'verified' => false,
        ]);
    }
}
