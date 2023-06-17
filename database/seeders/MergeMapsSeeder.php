<?php

namespace Database\Seeders;

use App\Models\FeatureProperties;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MergeMapsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $properties = FeatureProperties::with(['feature', 'feature.map'])->get();

        $properties->each(function ($property) {
            if (preg_match('/qa31/i', $property->id)) {
                $property->feature->update([
                    'map_id' => 1
                ]);
            } else if (preg_match('/to11/i', $property->id)) {
                $property->feature->update([
                    'map_id' => 13
                ]);
            }
        });
    }
}
