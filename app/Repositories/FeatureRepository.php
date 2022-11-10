<?php

namespace App\Repositories;

use App\Models\Feature;

class FeatureRepository
{
    private $features;

    public function __construct()
    {
        $this->features = Feature::with('properties', 'geometry.coordinates', 'owner')
            ->lazy();
    }

    public function all()
    {
        return $this->features;
    }

    public function sold()
    {
        return $this->features->reject(function($feature) {
            return $feature->owner->code === 'hm-2000000';
        });
    }
}
