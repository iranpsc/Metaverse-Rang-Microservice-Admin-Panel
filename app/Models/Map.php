<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
	protected $guarded = [];

    public $timestamps = false;

	public function crs()
	{
		return $this->hasMany(Cr::class);
	}

	public function features()
	{
		return $this->hasMany(Feature::class);
	}

    public function isPublished()
    {
        return $this->status === 1;
    }
}
