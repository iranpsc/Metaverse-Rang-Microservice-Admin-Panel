<?php

namespace Database\Factories\Challenge;

use App\Models\Challenge\Question;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'Q-'.Str::upper(Str::random(8)),
            'title' => fake()->sentence(4),
            'image' => 'questions/'.fake()->uuid().'.png',
            'creator_code' => (string) fake()->numberBetween(1000, 9999),
            'prize' => fake()->numberBetween(100, 5000),
        ];
    }
}
