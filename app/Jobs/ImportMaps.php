<?php

namespace App\Jobs;

use App\Models\Map;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportMaps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(private Map $map)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = file_get_contents(public_path('/uploads/maps/' . $this->map->fileName));
        $request = json_decode(explode('=', $file)[1], true);

        if ($request) {
            $crs = \App\Models\Crs::create([
                'type' => $request['crs']['type'],
                'map_id' => $this->map->id
            ]);
            \App\Models\CrsProperty::create([
                'name' => $request['crs']['properties']['name'],
                'crs_id' => $crs->id
            ]);

            foreach ($request['features'] as $feature) {
                $feature_db = \App\Models\Feature::create([
                    'type' => $feature['type'],
                    'map_id' => $this->map->id,
                    'owner_id' => 1,
                ]);
                $stability = $feature['properties']['density'] * $feature['properties']['area'];
                \App\Models\FeatureProperties::create([
                    'id' => $feature['properties']['id'],
                    'id_prefix' => explode('-', $feature['properties']['id'])[0],
                    'id_postfix' => explode('-', $feature['properties']['id'])[1],
                    'feature_id' => $feature_db->id,
                    'address' => $feature['properties']['address'] ?? '',
                    'density' => $feature['properties']['density'],
                    'date' => $feature['properties']['date'],
                    'stability' => $stability,
                    'label' => $feature['properties']['label'] ?? '',
                    'area' => $feature['properties']['area'],
                    'region' => $feature['properties']['Region'],
                    'karbari' => $feature['properties']['karbari'],
                    'owner' => $feature['properties']['owner'],
                    'rgb' => $feature['properties']['rgb'],
                ]);
                $geo = \App\Models\Geometry::create([
                    'type' => $feature['geometry']['type'],
                    'feature_id' => $feature_db->id
                ]);
                foreach ($feature['geometry']['coordinates'][0][0] as $cordinates) {
                    if (is_array($cordinates))
                        \App\Models\Coordinate::create([
                            'geometry_id' => $geo->id,
                            'x' => $cordinates[0],
                            'y' => $cordinates[1]
                        ]);
                }
            }
        }
    }
}
