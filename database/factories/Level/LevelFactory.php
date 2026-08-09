<?php

namespace Database\Factories\Level;

use App\Models\Level\Level;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Level>
 */
class LevelFactory extends Factory
{
    protected $model = Level::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'score' => (string) fake()->numberBetween(0, 10000),
            'background_image' => url('uploads/levels/'.fake()->uuid().'.jpg'),
        ];
    }

    public function withScore(int $score): static
    {
        return $this->state(fn () => [
            'score' => (string) $score,
        ]);
    }
}
