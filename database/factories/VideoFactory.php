<?php

namespace Database\Factories;

use App\Models\Video;
use App\Models\VideoSubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    protected $model = Video::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'video_sub_category_id' => VideoSubCategory::factory(),
            'title' => fake()->sentence(3),
            'slug' => Str::random(15),
            'description' => fake()->paragraph(),
            'fileName' => 'tutorials/videos/'.fake()->uuid().'.mp4',
            'creator_code' => strtolower(fake()->bothify('usr###')),
            'image' => 'tutorials/videos/'.fake()->uuid().'.jpg',
        ];
    }
}
