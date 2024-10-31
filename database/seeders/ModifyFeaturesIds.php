<?php

namespace Database\Seeders;

use App\Models\FeatureProperties;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModifyFeaturesIds extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FeatureProperties::chunk(100, function ($featureProperties) {
            foreach ($featureProperties as $featureProperty) {
                $id = explode('-', $featureProperty->id);

                if(count($id) !== 2) {
                    continue;
                }

                $featureProperty->update([
                    'id_prefix' => $id[0],
                    'id_postfix' => $id[1],
                ]);
            }
        });
    }
}
