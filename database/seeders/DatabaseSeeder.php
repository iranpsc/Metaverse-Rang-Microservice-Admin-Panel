<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\/User::factory()->count(1)->create();
        Video::all()->each(function ($video) {
            $video->update([
                'slug' => Str::random(10),
            ]);
        });
    }
}
