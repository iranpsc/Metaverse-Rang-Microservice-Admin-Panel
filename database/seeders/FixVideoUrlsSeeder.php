<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Video;
use App\Models\VideoCategory;
use App\Models\VideoSubCategory;

class FixVideoUrlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Video::all()->map(function ($video) {
            $video->update([
                'fileName' => ltrim(str_replace(url('uploads/'), '', $video->fileName), '/'),
                'image' => ltrim(str_replace(url('uploads/'), '', $video->image), '/'),
            ]);
        });

        VideoSubCategory::all()->map(function ($videoSubCategory) {
            $videoSubCategory->update([
                'image' => ltrim(str_replace(url('uploads/'), '', $videoSubCategory->image), '/'),
            ]);
        });

        VideoCategory::all()->map(function ($videoCategory) {
            $videoCategory->update([
                'image' => ltrim(str_replace(url('uploads/'), '', $videoCategory->image), '/'),
            ]);
        });
    }
}
