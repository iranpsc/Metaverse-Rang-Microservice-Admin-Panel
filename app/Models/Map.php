<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function status(): Attribute
    {
        return Attribute::make(fn ($value)  => $value ? 'منتشر شده' : 'در انتظار انتشار');
    }
}
