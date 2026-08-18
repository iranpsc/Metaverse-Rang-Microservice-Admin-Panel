<?php

namespace Database\Factories\Challenge;

use App\Models\Challenge\QuestionPrize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionPrize>
 */
class QuestionPrizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'amount' => random_int(1000, 2000),
        ];
    }
}
