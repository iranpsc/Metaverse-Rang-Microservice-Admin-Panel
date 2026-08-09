<?php

namespace Database\Factories\Level;

use App\Models\Level\Level;
use App\Models\Level\LevelGeneralInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LevelGeneralInfo>
 */
class LevelGeneralInfoFactory extends Factory
{
    protected $model = LevelGeneralInfo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'score' => fake()->numberBetween(0, 10000),
            'description' => fake()->paragraph(),
            'rank' => fake()->numberBetween(0, 100),
            'subcategories' => fake()->numberBetween(0, 50),
            'persian_font' => fake()->word(),
            'english_font' => fake()->word(),
            'file_volume' => fake()->randomFloat(3, 0, 100),
            'used_colors' => fake()->hexColor(),
            'points' => fake()->numberBetween(0, 5000),
            'designer' => fake()->name(),
            'model_designer' => fake()->name(),
            'creation_date' => fake()->date(),
            'lines' => fake()->numberBetween(0, 5000),
            'has_animation' => fake()->boolean(),
            'png_file' => null,
            'fbx_file' => null,
            'gif_file' => null,
        ];
    }

    /**
     * @param  array<string, string>  $files
     */
    public function withFbxFiles(array $files): static
    {
        return $this->state(fn () => [
            'fbx_file' => $files,
        ]);
    }

    public function withPngFile(?string $url = null): static
    {
        return $this->state(fn () => [
            'png_file' => $url ?? url('uploads/levels/sample.png'),
        ]);
    }

    public function withGifFile(?string $url = null): static
    {
        return $this->state(fn () => [
            'gif_file' => $url ?? url('uploads/levels/sample.gif'),
        ]);
    }
}
