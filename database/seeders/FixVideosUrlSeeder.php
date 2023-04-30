<?php

namespace Database\Seeders;

use App\Models\Video;
use App\Models\VideoCategory;
use App\Models\VideoSubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FixVideosUrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Video::each(function($video) {
            $video->update([
                'fileName' => url('uploads/'.$video->fileName),
                'image' => url('uploads/'.$video->image)
            ]);
        });

        VideoCategory::each(function($category) {
            $category->update(['image' => url('uploads/'.$category->image)]);
        });

        VideoSubCategory::each(function($category) {
            $category->update(['image' => url('uploads/'.$category->image)]);
        });
    }
}
