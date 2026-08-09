<?php

namespace Database\Factories;

use App\Models\VideoCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VideoCategory>
 */
class VideoCategoryFactory extends Factory
{
    protected $model = VideoCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->sentence(12),
            'image' => 'tutorials/'.fake()->uuid().'.jpg',
            'icon' => 'tutorials/'.fake()->uuid().'.svg',
        ];
    }
}
