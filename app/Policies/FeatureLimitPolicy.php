<?php

namespace App\Policies;

use App\Models\FeatureLimit;
use Illuminate\Contracts\Auth\Authenticatable;

class FeatureLimitPolicy
{
    /**
     * Determine whether the feature limit can be deleted.
     * Expired limitations must not be deleted.
     */
    public function delete(?Authenticatable $user, FeatureLimit $featureLimit): bool
    {
        return $featureLimit->isDeletable();
    }
}
