<?php

namespace Database\Factories;

use App\Models\VideoCategory;
use App\Models\VideoSubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VideoSubCategory>
 */
class VideoSubCategoryFactory extends Factory
{
    protected $model = VideoSubCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'video_category_id' => VideoCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->sentence(10),
            'image' => 'tutorials/sub/'.fake()->uuid().'.jpg',
            'icon' => 'tutorials/sub/'.fake()->uuid().'.svg',
        ];
    }
}
