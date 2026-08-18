<?php

namespace Database\Factories\Level;

use App\Models\Level\Level;
use App\Models\Level\LevelGem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LevelGem>
 */
class LevelGemFactory extends Factory
{
    protected $model = LevelGem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'thread' => fake()->word(),
            'points' => fake()->numberBetween(0, 1000),
            'volume' => fake()->randomFloat(3, 0, 100),
            'color' => fake()->hexColor(),
            'png_file' => null,
            'fbx_file' => null,
            'encryption' => false,
            'has_animation' => false,
            'lines' => fake()->numberBetween(0, 500),
            'designer' => fake()->name(),
        ];
    }

    public function withPng(): static
    {
        return $this->state(fn () => [
            'png_file' => url('uploads/levels/'.fake()->uuid().'.png'),
        ]);
    }

    public function withFbx(): static
    {
        return $this->state(fn () => [
            'fbx_file' => [
                'fbx' => url('uploads/levels/'.fake()->uuid().'.fbx'),
            ],
        ]);
    }
}
