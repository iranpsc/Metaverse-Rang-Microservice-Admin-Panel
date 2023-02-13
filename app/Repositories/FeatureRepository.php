<?php

namespace App\Repositories;

use App\Models\Feature;

class FeatureRepository
{
    public function all()
    {
        return Feature::count();
    }

    public function sold()
    {
        return Feature::whereNot('owner_id', 1)->count();
    }
}
