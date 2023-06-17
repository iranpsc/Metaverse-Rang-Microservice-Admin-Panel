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
        $qa31Maps = \App\Models\Map::whereName('QA31')->get();

        $firstQA31Map = $qa31Maps->first();

        $firstQA31Map->update([
            'polygon_count' => $qa31Maps->sum('polygon_count'),
            'total_area' => $qa31Maps->sum('total_area'),
            'last_id' => $qa31Maps->max('last_id'),
        ]);

        $qa31Maps->where('id', '!=', $firstQA31Map->id)->each(function ($map) {
            $map->delete();
        });

        $to11Maps = \App\Models\Map::whereName('TO11')->get();

        $firstTO11Map = $to11Maps->first();

        $firstTO11Map->update([
            'polygon_count' => $to11Maps->sum('polygon_count'),
            'total_area' => $to11Maps->sum('total_area'),
            'last_id' => $to11Maps->max('last_id'),
        ]);

        $to11Maps->where('id', '!=', $firstTO11Map->id)->each(function ($map) {
            $map->delete();
        });

        $properties = FeatureProperties::with(['feature', 'feature.map'])->get();

        $properties->each(function ($property) use ($firstQA31Map, $firstTO11Map) {
            if (preg_match('/^qa31/$', $property->id)) {
                $property->feature->update([
                    'map_id' => $firstQA31Map->id
                ]);
            } else if (preg_match('/^to11/$', $property->id)) {
                $property->feature->update([
                    'map_id' => $firstTO11Map->id
                ]);
            }
        });
    }
}
