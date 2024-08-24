<?php

namespace Database\Seeders;

use App\Models\FeatureProperties;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResetFeaturePropertiesRgb extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FeatureProperties::whereHas('feature', function ($query) {
            $query->where('ower_id', 1);
        })
        ->chunk(100, function ($featureProperties) {
            foreach ($featureProperties as $featureProperty) {
                switch ($featureProperty->karbari) {
                    case 'm':
                        $featureProperty->update([
                            'stability' => $featureProperty->area * $featureProperty->density,
                            'rgb' => 'a'
                        ]);
                        break;
                    case 't':
                        $featureProperty->update([
                            'stability' => $featureProperty->area * $featureProperty->density,
                            'rgb' => 'h'
                        ]);
                        break;
                    case 'a':
                        $featureProperty->update([
                            'stability' => $featureProperty->area * $featureProperty->density,
                            'rgb' => 'o'
                        ]);
                        break;
                    default:
                        break;
                }
            }
        });
    }
}
