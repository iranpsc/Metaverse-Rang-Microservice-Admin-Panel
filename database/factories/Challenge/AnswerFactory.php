<?php

namespace Database\Factories\Challenge;

use App\Models\Challenge\Answer;
use App\Models\Challenge\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'title' => fake()->sentence(3),
            'image' => 'answers/'.fake()->uuid().'.png',
            'is_correct' => false,
        ];
    }

    public function correct(): static
    {
        return $this->state(fn () => ['is_correct' => true]);
    }
}
