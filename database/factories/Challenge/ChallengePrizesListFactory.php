<?php

namespace Database\Factories\Challenge;

use App\Models\Challenge\ChallengePrizesList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChallengePrizesList>
 */
class ChallengePrizesListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name,
        ];
    }
}
