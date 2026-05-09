<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $table = 'features';

    protected $primaryKey = 'id';

    protected $casts = [
        'map_id' => 'int',
        'owner_id' => 'int',

    ];

    protected $fillable = [
        'map_id',
        'type',
        'owner_id',
    ];

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function properties()
    {
        return $this->hasOne(FeatureProperties::class, 'feature_id', 'id');
    }

    public function geometry()
    {
        return $this->hasOne(Geometry::class,  'feature_id', 'id');
    }
    public function images()
    {
        return $this->hasMany(FeatureImage::class);
    }
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function buyRequests()
    {
        return $this->hasMany(BuyFeatureRequest::class, 'feature_id');
    }

    public function sellRequests()
    {
        return $this->hasMany(SellFeatureRequests::class, 'feature_id');
    }

    public function lockedAssets()
    {
        return $this->morphOne(LockedAsset::class, 'assetable');
    }
}
