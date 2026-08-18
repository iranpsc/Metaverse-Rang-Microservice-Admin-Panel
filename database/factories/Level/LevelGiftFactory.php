<?php

namespace Database\Factories\Level;

use App\Models\Level\Level;
use App\Models\Level\LevelGift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LevelGift>
 */
class LevelGiftFactory extends Factory
{
    protected $model = LevelGift::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'monthly_capacity_count' => fake()->numberBetween(0, 100),
            'store_capacity' => fake()->boolean(),
            'sell_capacity' => fake()->boolean(),
            'features' => fake()->sentence(),
            'sell' => fake()->boolean(),
            'vod_document_registration' => fake()->boolean(),
            'seller_link' => fake()->url(),
            'designer' => fake()->name(),
            'three_d_model_volume' => fake()->randomFloat(4, 0, 100),
            'three_d_model_points' => fake()->numberBetween(0, 5000),
            'three_d_model_lines' => fake()->numberBetween(0, 5000),
            'has_animation' => fake()->boolean(),
            'png_file' => null,
            'fbx_file' => null,
            'gif_file' => null,
            'rent' => fake()->boolean(),
            'vod_count' => fake()->numberBetween(0, 50),
            'start_vod_id' => null,
            'end_vod_id' => null,
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
